<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;

/**
 * Создание дампов БД PostgreSQL на диск с ротацией (хранится не более N последних).
 */
class DatabaseBackupService
{
    /**
     * Единственные таблицы Sylius, которые попадают в дамп: вход в админку
     * (вместе с sylius_avatar_image — на неё смотрит FK из sylius_admin_user,
     * без неё clean-восстановление дампа падает) и история миграций.
     * Вся остальная инфраструктура Sylius из дампа исключается.
     */
    public const KEPT_SYSTEM_TABLES = [
        'sylius_admin_user',
        'sylius_avatar_image',
        'sylius_migrations',
    ];

    public function __construct(
        private Connection $connection,
        private string $backupDir,
        private LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Готовый процесс pg_dump (со списком исключаемых таблиц). Запускать вызывающему
     * коду со своим колбэком вывода (в файл или в поток).
     */
    public function createDumpProcess(): Process
    {
        $params   = $this->connection->getParams();
        $host     = (string) ($params['host'] ?? '127.0.0.1');
        $port     = (string) ($params['port'] ?? 5432);
        $user     = (string) ($params['user'] ?? 'postgres');
        $password = (string) ($params['password'] ?? '');
        $dbname   = (string) ($params['dbname'] ?? '');

        $command = [
            'pg_dump',
            '--host', $host,
            '--port', $port,
            '--username', $user,
            '--no-owner',
            '--no-privileges',
            '--clean',
            '--if-exists',
        ];
        // Дампим только нужные таблицы (их последовательности/индексы попадают автоматически),
        // вся инфраструктура Sylius и её sequence'ы исключаются.
        foreach ($this->dumpableTables() as $table) {
            $command[] = '--table=public.' . $table;
        }
        $command[] = $dbname;

        return new Process($command, null, ['PGPASSWORD' => $password], null, 1800.0);
    }

    /**
     * Список таблиц для дампа: все собственные таблицы приложения (не sylius_*)
     * плюс sylius_admin_user и sylius_migrations.
     *
     * @return string[]
     */
    public function dumpableTables(): array
    {
        $tables = $this->connection->fetchFirstColumn(
            "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename",
        );

        return array_values(array_filter(
            $tables,
            static fn (string $t): bool => !str_starts_with($t, 'sylius_') || in_array($t, self::KEPT_SYSTEM_TABLES, true),
        ));
    }

    /**
     * Создаёт новый дамп БД. Возвращает абсолютный путь к файлу.
     */
    public function createBackup(): string
    {
        $this->ensureDir();

        $path = sprintf('%s/db-dump-%s.sql', $this->backupDir, date('Ymd-His'));

        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Не удалось открыть файл для записи дампа: ' . $path);
        }

        $process = $this->createDumpProcess();

        try {
            $process->run(function (string $type, string $buffer) use ($handle): void {
                if (Process::OUT === $type) {
                    fwrite($handle, $buffer);
                }
            });
        } finally {
            fclose($handle);
        }

        if (!$process->isSuccessful()) {
            @unlink($path);
            $this->logger->error('Бэкап БД не удался', ['error' => $process->getErrorOutput()]);
            throw new \RuntimeException('pg_dump завершился с ошибкой: ' . trim($process->getErrorOutput()));
        }

        $this->logger->info('Создан бэкап БД', ['path' => $path]);

        return $path;
    }

    /**
     * Оставляет только $keep самых свежих дампов, остальные (самые старые) удаляет.
     *
     * @return string[] имена удалённых файлов
     */
    public function rotate(int $keep = 14): array
    {
        $keep = max(1, $keep);
        $files = $this->listBackups();

        $deleted = [];
        foreach (array_slice($files, $keep) as $old) {
            if (@unlink($old)) {
                $deleted[] = basename($old);
                $this->logger->info('Удалён старый бэкап БД', ['file' => basename($old)]);
            }
        }

        return $deleted;
    }

    /**
     * Список файлов дампов, отсортированный от самого свежего к самому старому.
     *
     * @return string[] абсолютные пути
     */
    public function listBackups(): array
    {
        $files = glob($this->backupDir . '/db-dump-*.sql') ?: [];
        usort($files, static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a));

        return $files;
    }

    /**
     * Возраст самого свежего дампа в днях (null — если дампов нет).
     */
    public function latestBackupAgeDays(): ?float
    {
        $files = $this->listBackups();
        if ($files === []) {
            return null;
        }

        return (time() - filemtime($files[0])) / 86400;
    }

    private function ensureDir(): void
    {
        if (!is_dir($this->backupDir) && !mkdir($this->backupDir, 0775, true) && !is_dir($this->backupDir)) {
            throw new \RuntimeException('Не удалось создать директорию для бэкапов: ' . $this->backupDir);
        }
    }
}
