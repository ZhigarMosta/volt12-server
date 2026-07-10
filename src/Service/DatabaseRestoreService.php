<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;

/**
 * Восстановление БД из SQL-дампа pg_dump с многослойной проверкой содержимого.
 *
 * Слои защиты:
 *  1. Дамп принимается только в plain-формате pg_dump (проверка заголовка).
 *  2. Whitelist: разрешён фиксированный набор типов SQL-команд, которые реально
 *     встречаются в наших дампах (SET, CREATE/DROP/ALTER TABLE|SEQUENCE|INDEX в схеме
 *     public, COPY ... FROM stdin, setval). Всё прочее — отказ.
 *  3. Blacklist: явные запреты опасных конструкций даже внутри разрешённых команд
 *     (функции, роли, расширения, GRANT/REVOKE, DO-блоки, COPY PROGRAM, pg_read_file...).
 *  4. Метакоманды psql запрещены, кроме пары \restrict/\unrestrict, которую пишет
 *     сам pg_dump (защита от инъекции метакоманд).
 *  5. Только схема public: объекты в других схемах — отказ.
 *  6. «Ничего лишнего не создаёт»: все таблицы и sequence'ы дампа обязаны уже
 *     существовать в текущей БД, иначе отказ с перечнем лишних объектов.
 *  7. Валидный UTF-8 и лимит размера файла.
 *
 * Выполнение — через psql в ОДНОЙ транзакции (--single-transaction + ON_ERROR_STOP):
 * любая ошибка откатывает всё целиком, БД остаётся в исходном состоянии.
 * Перед восстановлением автоматически создаётся страховочный дамп текущей БД.
 */
class DatabaseRestoreService
{
    public const MAX_FILE_SIZE = 1024 * 1024 * 1024; // 1 ГБ

    private const ALLOWED_STATEMENTS = [
        '~^SET\s+[a-z_]+\s*=\s*[^;]+;$~i',
        '~^SELECT pg_catalog\.set_config\(\'search_path\', \'\', false\);$~',
        '~^SELECT pg_catalog\.setval\(\'public\."?[a-z0-9_]+"?\',\s*\d+,\s*(?:true|false)\);$~i',
        '~^DROP TABLE IF EXISTS public\."?[a-z0-9_]+"?;$~i',
        '~^DROP SEQUENCE IF EXISTS public\."?[a-z0-9_]+"?;$~i',
        '~^DROP INDEX IF EXISTS public\."?[a-z0-9_]+"?;$~i',
        '~^ALTER TABLE (?:IF EXISTS )?(?:ONLY )?public\."?[a-z0-9_]+"?\s~i',
        '~^CREATE TABLE public\."?[a-z0-9_]+"?\s*\(~i',
        '~^CREATE SEQUENCE public\."?[a-z0-9_]+"?~i',
        '~^ALTER SEQUENCE public\."?[a-z0-9_]+"?~i',
        '~^CREATE (?:UNIQUE )?INDEX "?[a-z0-9_]+"? ON public\."?[a-z0-9_]+"?~i',
        '~^COPY public\."?[a-z0-9_]+"? \([^)]*\) FROM stdin;$~i',
        '~^COMMENT ON (?:TABLE|COLUMN|INDEX|SEQUENCE) public\.~i',
    ];

    private const FORBIDDEN_PATTERNS = [
        '~\bCREATE\s+(?:OR\s+REPLACE\s+)?(?:FUNCTION|PROCEDURE|TRIGGER|RULE|EXTENSION|SCHEMA|DATABASE|ROLE|USER|POLICY|PUBLICATION|SUBSCRIPTION|SERVER|CAST|AGGREGATE|OPERATOR|COLLATION|DOMAIN|TYPE|VIEW|MATERIALIZED)~i',
        '~\b(?:ALTER|DROP)\s+(?:DATABASE|ROLE|USER|SYSTEM|SCHEMA|EXTENSION|FUNCTION|PROCEDURE|TRIGGER|VIEW|POLICY)~i',
        '~\bDO\s+\$~i',
        '~\bSECURITY\s+DEFINER\b~i',
        '~\bGRANT\b~i',
        '~\bREVOKE\b~i',
        '~\bOWNER\s+TO\b~i',
        '~\bSET\s+(?:ROLE|SESSION\s+AUTHORIZATION)\b~i',
        '~\bCOPY\b[^;]*\b(?:PROGRAM|TO\s+STDOUT|TO\s+\')~i',
        '~\b(?:pg_read_file|pg_write_file|pg_ls_dir|pg_read_binary_file|pg_stat_file|lo_import|lo_export|dblink|pg_sleep)\s*\(~i',
        '~\bTRUNCATE\b~i',
        '~\bINSERT\s+INTO\b~i', // наши дампы используют COPY; INSERT — признак постороннего файла
    ];

    public function __construct(
        private Connection $connection,
        private DatabaseBackupService $backupService,
        private string $backupDir,
        private LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Полная проверка файла дампа без его выполнения.
     *
     * @return array{ok: bool, errors: string[], warnings: string[], stats: array<string, mixed>}
     */
    public function validateDumpFile(string $path): array
    {
        $errors = [];
        $warnings = [];
        $stats = [
            'size' => 0,
            'tables' => [],
            'rows' => 0,
            'statements' => 0,
            'dump_version' => null,
        ];

        if (!is_file($path) || !is_readable($path)) {
            return ['ok' => false, 'errors' => ['Файл дампа не найден или недоступен для чтения.'], 'warnings' => [], 'stats' => $stats];
        }

        $size = (int) filesize($path);
        $stats['size'] = $size;
        if ($size === 0) {
            return ['ok' => false, 'errors' => ['Файл дампа пуст.'], 'warnings' => [], 'stats' => $stats];
        }
        if ($size > self::MAX_FILE_SIZE) {
            return ['ok' => false, 'errors' => [sprintf('Файл больше лимита %d МБ.', self::MAX_FILE_SIZE / 1024 / 1024)], 'warnings' => [], 'stats' => $stats];
        }

        $head = (string) file_get_contents($path, false, null, 0, 4096);
        if (!str_contains($head, 'PostgreSQL database dump')) {
            return ['ok' => false, 'errors' => ['Это не plain-дамп pg_dump: в заголовке нет строки «PostgreSQL database dump».'], 'warnings' => [], 'stats' => $stats];
        }
        if (preg_match('~-- Dumped by pg_dump version ([\d.]+)~', $head, $m)) {
            $stats['dump_version'] = $m[1];
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return ['ok' => false, 'errors' => ['Не удалось открыть файл дампа.'], 'warnings' => [], 'stats' => $stats];
        }

        $inCopyData = false;
        $statement = '';
        $lineNo = 0;
        $tables = [];
        $rows = 0;

        try {
            while (($line = fgets($handle)) !== false) {
                $lineNo++;

                if (!mb_check_encoding($line, 'UTF-8')) {
                    $errors[] = sprintf('Строка %d: невалидный UTF-8.', $lineNo);
                    if (count($errors) > 20) {
                        break;
                    }
                    continue;
                }

                // Данные COPY-блока: не SQL, проверяем только конец блока
                if ($inCopyData) {
                    if (rtrim($line, "\r\n") === '\.') {
                        $inCopyData = false;
                    } else {
                        $rows++;
                    }
                    continue;
                }

                $trimmed = trim($line);

                if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                    continue;
                }

                // Метакоманды psql: разрешена только пара \restrict/\unrestrict от pg_dump
                if (str_starts_with($trimmed, '\\')) {
                    if ($statement !== '') {
                        $errors[] = sprintf('Строка %d: метакоманда внутри SQL-выражения.', $lineNo);
                    } elseif (!preg_match('~^\\\\(?:restrict|unrestrict)\s+[A-Za-z0-9]+$~', $trimmed)) {
                        $errors[] = sprintf('Строка %d: запрещённая метакоманда psql «%s».', $lineNo, mb_substr($trimmed, 0, 40));
                    }
                    continue;
                }

                $statement .= ($statement === '' ? '' : ' ') . $trimmed;

                // COPY ... FROM stdin; — начало блока данных
                if (!str_ends_with($trimmed, ';')) {
                    continue;
                }

                $stats['statements']++;
                $this->validateStatement($statement, $lineNo, $errors, $tables);

                if (preg_match('~^COPY public\."?([a-z0-9_]+)"?.* FROM stdin;$~i', $statement)) {
                    $inCopyData = true;
                }

                $statement = '';

                if (count($errors) > 20) {
                    $errors[] = 'Слишком много ошибок — проверка остановлена.';
                    break;
                }
            }
        } finally {
            fclose($handle);
        }

        if ($inCopyData) {
            $errors[] = 'Файл оборван: COPY-блок данных не завершён маркером «\.».';
        }
        if ($statement !== '') {
            $errors[] = 'Файл оборван: последнее SQL-выражение не завершено.';
        }

        $stats['tables'] = array_keys($tables);
        $stats['rows'] = $rows;

        // «Ничего лишнего не создаёт»: все объекты дампа должны уже существовать в БД
        if ($errors === []) {
            $existingTables = $this->connection->fetchFirstColumn(
                "SELECT tablename FROM pg_tables WHERE schemaname = 'public'",
            );
            $existingSequences = $this->connection->fetchFirstColumn(
                "SELECT c.relname FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace WHERE c.relkind = 'S' AND n.nspname = 'public'",
            );
            $known = array_flip(array_merge($existingTables, $existingSequences));

            foreach (array_keys($tables) as $name) {
                if (!isset($known[$name])) {
                    $errors[] = sprintf(
                        'Дамп создаёт объект «%s», которого нет в текущей БД. Если это дамп более новой версии — сначала выполните миграции (php bin/console doctrine:migrations:migrate).',
                        $name,
                    );
                }
            }

            // Подсказка в обратную сторону: чего из текущей БД в дампе нет
            $appTables = $this->backupService->dumpableTables();
            $missingInDump = array_diff($appTables, array_keys($tables));
            if ($missingInDump !== []) {
                $warnings[] = sprintf(
                    'В дампе нет таблиц: %s. После восстановления выполните миграции — они создадут недостающее.',
                    implode(', ', $missingInDump),
                );
            }
        }

        return ['ok' => $errors === [], 'errors' => $errors, 'warnings' => $warnings, 'stats' => $stats];
    }

    /**
     * Восстанавливает БД из проверенного дампа. Возвращает отчёт.
     *
     * @return array{pre_backup: string, stats: array<string, mixed>, warnings: string[]}
     */
    public function restore(string $path): array
    {
        $validation = $this->validateDumpFile($path);
        if (!$validation['ok']) {
            throw new \RuntimeException('Дамп не прошёл проверку: ' . implode(' ', $validation['errors']));
        }

        // Страховочный дамп текущего состояния — на случай, если восстановили не то
        $preBackupPath = $this->backupService->createBackup();
        $this->backupService->rotate();

        $params   = $this->connection->getParams();
        $process = new Process(
            [
                'psql',
                '--host', (string) ($params['host'] ?? '127.0.0.1'),
                '--port', (string) ($params['port'] ?? 5432),
                '--username', (string) ($params['user'] ?? 'postgres'),
                '--no-psqlrc',
                '--single-transaction',
                '--set', 'ON_ERROR_STOP=1',
                '--quiet',
                '--file', $path,
                (string) ($params['dbname'] ?? ''),
            ],
            null,
            ['PGPASSWORD' => (string) ($params['password'] ?? '')],
            null,
            1800.0,
        );

        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->error('Восстановление БД не удалось (транзакция откатена)', [
                'dump' => basename($path),
                'error' => $process->getErrorOutput(),
            ]);
            throw new \RuntimeException(
                'psql завершился с ошибкой, транзакция откатена — БД осталась без изменений. ' . trim($process->getErrorOutput()),
            );
        }

        $this->logger->warning('БД восстановлена из дампа', [
            'dump' => basename($path),
            'pre_backup' => basename($preBackupPath),
        ]);

        return [
            'pre_backup' => basename($preBackupPath),
            'stats' => $validation['stats'],
            'warnings' => $validation['warnings'],
        ];
    }

    /**
     * Абсолютный путь к дампу по типу и штампу из URL (без точек в маршруте).
     * kind: dump — автодампы, upload — загруженные вручную файлы.
     */
    public function resolveDumpPath(string $kind, string $stamp): ?string
    {
        if (!in_array($kind, ['dump', 'upload'], true) || !preg_match('~^\d{8}-\d{6}$~', $stamp)) {
            return null;
        }

        $path = sprintf('%s/db-%s-%s.sql', $this->backupDir, $kind, $stamp);

        return is_file($path) ? $path : null;
    }

    /**
     * Сохраняет загруженный вручную дамп рядом с автодампами (db-upload-*.sql),
     * храня не более 5 последних загрузок.
     *
     * @return array{kind: string, stamp: string}
     */
    public function storeUploadedDump(string $tmpPath): array
    {
        if (!is_dir($this->backupDir) && !mkdir($this->backupDir, 0775, true) && !is_dir($this->backupDir)) {
            throw new \RuntimeException('Не удалось создать директорию для дампов.');
        }

        $stamp = date('Ymd-His');
        $target = sprintf('%s/db-upload-%s.sql', $this->backupDir, $stamp);

        if (!@rename($tmpPath, $target) && !@copy($tmpPath, $target)) {
            throw new \RuntimeException('Не удалось сохранить загруженный дамп.');
        }
        @chmod($target, 0664);

        $uploads = glob($this->backupDir . '/db-upload-*.sql') ?: [];
        usort($uploads, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));
        foreach (array_slice($uploads, 5) as $old) {
            @unlink($old);
        }

        return ['kind' => 'upload', 'stamp' => $stamp];
    }

    /**
     * @param string[] $errors
     * @param array<string, true> $tables
     */
    private function validateStatement(string $statement, int $lineNo, array &$errors, array &$tables): void
    {
        foreach (self::FORBIDDEN_PATTERNS as $pattern) {
            if (preg_match($pattern, $statement)) {
                $errors[] = sprintf(
                    'Строка ~%d: запрещённая конструкция (%s) в «%s…».',
                    $lineNo,
                    trim($pattern, '~i'),
                    mb_substr($statement, 0, 80),
                );

                return;
            }
        }

        foreach (self::ALLOWED_STATEMENTS as $pattern) {
            if (preg_match($pattern, $statement)) {
                // Собираем имена объектов (таблицы и sequence) для проверки «ничего лишнего»
                if (preg_match('~^(?:CREATE TABLE|COPY|DROP TABLE IF EXISTS|CREATE SEQUENCE|DROP SEQUENCE IF EXISTS)\s+public\."?([a-z0-9_]+)"?~i', $statement, $m)) {
                    $tables[strtolower($m[1])] = true;
                }

                // Любая ссылка на чужую схему внутри разрешённой команды — отказ.
                // Строковые литералы вырезаем, чтобы точки в данных не давали ложных срабатываний.
                $withoutStrings = preg_replace("~'(?:[^']|'')*'~s", "''", $statement) ?? $statement;
                if (preg_match_all('~\b([a-z_][a-z0-9_]*)\.[a-z_][a-z0-9_]*~i', $withoutStrings, $refs)) {
                    foreach (array_unique(array_map('strtolower', $refs[1])) as $schema) {
                        if (!in_array($schema, ['public', 'pg_catalog'], true)) {
                            $errors[] = sprintf('Строка ~%d: обращение к схеме «%s», разрешена только public.', $lineNo, $schema);

                            return;
                        }
                    }
                }

                return;
            }
        }

        $errors[] = sprintf(
            'Строка ~%d: команда не входит в список разрешённых: «%s…».',
            $lineNo,
            mb_substr($statement, 0, 80),
        );
    }
}
