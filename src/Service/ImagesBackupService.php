<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Выгрузка всех картинок сайта (public/uploads) в zip-архив и восстановление
 * из загруженного архива с проверками безопасности:
 *  - пути только внутри uploads/, без «..», абсолютных путей и симлинков;
 *  - только разрешённые расширения изображений;
 *  - лимиты на количество файлов, размер файла и суммарный размер;
 *  - проверка реального MIME-типа содержимого (файл действительно картинка).
 */
class ImagesBackupService
{
    private const ALLOWED_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'avif'];
    private const ALLOWED_MIME = ['image/webp', 'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/avif'];
    private const MAX_ENTRIES = 20000;
    private const MAX_ENTRY_SIZE = 50 * 1024 * 1024;      // 50 МБ на файл
    private const MAX_TOTAL_SIZE = 2 * 1024 * 1024 * 1024; // 2 ГБ суммарно

    public function __construct(
        private string $publicDir,
        private LoggerInterface $logger = new NullLogger(),
    ) {}

    /**
     * Собирает zip со всеми файлами из public/uploads (пути в архиве — uploads/...).
     * Возвращает путь к временному файлу архива — удалить после отдачи.
     */
    public function createArchive(): string
    {
        $uploadsDir = $this->publicDir . '/uploads';
        if (!is_dir($uploadsDir)) {
            throw new \RuntimeException('Директория public/uploads не найдена.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'volt12-images-');
        if ($zipPath === false) {
            throw new \RuntimeException('Не удалось создать временный файл архива.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Не удалось открыть архив для записи.');
        }

        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadsDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY,
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->isLink()) {
                continue;
            }
            $local = 'uploads/' . ltrim(str_replace($uploadsDir, '', $file->getPathname()), '/');
            $zip->addFile($file->getPathname(), $local);
            $count++;
        }

        $zip->close();
        $this->logger->info('Собран архив картинок', ['files' => $count, 'size' => filesize($zipPath)]);

        return $zipPath;
    }

    /**
     * Восстанавливает картинки из zip-архива.
     *
     * @return array{restored: int, skipped: array<int, array{entry: string, reason: string}>}
     */
    public function restoreArchive(string $zipPath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Файл не является корректным zip-архивом.');
        }

        if ($zip->numFiles > self::MAX_ENTRIES) {
            $zip->close();
            throw new \RuntimeException(sprintf('В архиве слишком много файлов (лимит %d).', self::MAX_ENTRIES));
        }

        $restored = 0;
        $skipped = [];
        $totalSize = 0;
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $uploadsRoot = $this->publicDir . '/uploads';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                continue;
            }
            $entry = (string) $stat['name'];

            // Каталоги пропускаем молча
            if (str_ends_with($entry, '/')) {
                continue;
            }

            if ($reason = $this->rejectEntry($entry, (int) $stat['size'], $totalSize)) {
                $skipped[] = ['entry' => $entry, 'reason' => $reason];
                continue;
            }

            $stream = $zip->getStream($entry);
            if ($stream === false) {
                $skipped[] = ['entry' => $entry, 'reason' => 'не удалось прочитать из архива'];
                continue;
            }
            $content = stream_get_contents($stream, self::MAX_ENTRY_SIZE + 1);
            fclose($stream);

            if ($content === false || strlen($content) > self::MAX_ENTRY_SIZE) {
                $skipped[] = ['entry' => $entry, 'reason' => 'файл больше лимита'];
                continue;
            }

            // Содержимое обязано быть картинкой, а не просто файлом с нужным расширением
            $mime = (string) $finfo->buffer($content);
            if (!in_array($mime, self::ALLOWED_MIME, true)) {
                $skipped[] = ['entry' => $entry, 'reason' => 'содержимое не является изображением (' . $mime . ')'];
                continue;
            }

            $target = $this->publicDir . '/' . $entry;
            $targetDir = dirname($target);

            if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
                $skipped[] = ['entry' => $entry, 'reason' => 'не удалось создать директорию'];
                continue;
            }

            // Финальная страховка: путь после нормализации обязан остаться внутри uploads/
            $realDir = realpath($targetDir);
            if ($realDir === false || !str_starts_with($realDir . '/', realpath($uploadsRoot) . '/')) {
                $skipped[] = ['entry' => $entry, 'reason' => 'путь выходит за пределы uploads'];
                continue;
            }

            if (file_put_contents($target, $content) === false) {
                $skipped[] = ['entry' => $entry, 'reason' => 'не удалось записать файл'];
                continue;
            }

            $totalSize += strlen($content);
            $restored++;
        }

        $zip->close();

        $this->logger->warning('Восстановлены картинки из архива', ['restored' => $restored, 'skipped' => count($skipped)]);

        return ['restored' => $restored, 'skipped' => $skipped];
    }

    private function rejectEntry(string $entry, int $size, int $totalSize): ?string
    {
        if (str_contains($entry, '..') || str_contains($entry, '\\') || str_starts_with($entry, '/')) {
            return 'подозрительный путь';
        }

        if (!preg_match('~^uploads/(?:[A-Za-z0-9._-]+/)*[A-Za-z0-9._-]+$~', $entry)) {
            return 'путь вне uploads/ или содержит недопустимые символы';
        }

        $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return 'недопустимое расширение .' . $ext;
        }

        if ($size > self::MAX_ENTRY_SIZE) {
            return 'файл больше лимита';
        }

        if ($totalSize + $size > self::MAX_TOTAL_SIZE) {
            return 'превышен суммарный лимит архива';
        }

        return null;
    }
}
