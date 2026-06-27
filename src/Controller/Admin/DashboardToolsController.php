<?php

namespace App\Controller\Admin;

use App\Service\DatabaseBackupService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tools')]
class DashboardToolsController extends AbstractController
{
    public function __construct(
        private Connection $connection,
    ) {}

    /**
     * Полная выгрузка базы данных PostgreSQL в SQL-файл (стримом, без буфера в памяти).
     */
    #[Route('/db-export', name: 'app_admin_db_export', methods: ['GET'])]
    public function dbExport(DatabaseBackupService $backupService): StreamedResponse
    {
        $dbname = (string) ($this->connection->getParams()['dbname'] ?? 'app');

        $filename = sprintf('db-dump-%s-%s.sql', $dbname, (new \DateTime())->format('Ymd-His'));

        $response = new StreamedResponse(function () use ($backupService): void {
            $process = $backupService->createDumpProcess();

            $process->run(function (string $type, string $buffer): void {
                if (Process::OUT === $type) {
                    echo $buffer;
                    @ob_flush();
                    flush();
                }
            });

            if (!$process->isSuccessful()) {
                echo "\n-- ОШИБКА pg_dump:\n-- " . str_replace("\n", "\n-- ", trim($process->getErrorOutput())) . "\n";
            }
        });

        $response->headers->set('Content-Type', 'application/sql; charset=UTF-8');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename),
        );

        return $response;
    }

    /**
     * Скачивание конкретного сохранённого автодампа.
     * Имя ограничено шаблоном дампа и сверяется со списком сервиса (защита от path traversal).
     */
    #[Route(
        '/db-backup/{stamp}',
        name: 'app_admin_db_backup_download',
        requirements: ['stamp' => '\d{8}-\d{6}'],
        methods: ['GET'],
    )]
    public function downloadBackup(string $stamp, DatabaseBackupService $backupService): Response
    {
        // Без расширения в URL: php -S отдаёт пути с точкой как статику и не доходит до Symfony.
        $name = sprintf('db-dump-%s.sql', $stamp);

        foreach ($backupService->listBackups() as $path) {
            if (basename($path) === $name) {
                return $this->file($path);
            }
        }

        throw $this->createNotFoundException('Дамп не найден: ' . $name);
    }
}

