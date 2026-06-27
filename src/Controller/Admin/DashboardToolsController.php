<?php

namespace App\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function dbExport(): StreamedResponse
    {
        $params = $this->connection->getParams();
        $host     = (string) ($params['host'] ?? '127.0.0.1');
        $port     = (string) ($params['port'] ?? 5432);
        $user     = (string) ($params['user'] ?? 'postgres');
        $password = (string) ($params['password'] ?? '');
        $dbname   = (string) ($params['dbname'] ?? '');

        $filename = sprintf('db-dump-%s-%s.sql', $dbname, (new \DateTime())->format('Ymd-His'));

        $response = new StreamedResponse(function () use ($host, $port, $user, $password, $dbname): void {
            $process = new Process(
                [
                    'pg_dump',
                    '--host', $host,
                    '--port', $port,
                    '--username', $user,
                    '--no-owner',
                    '--no-privileges',
                    '--clean',
                    '--if-exists',
                    $dbname,
                ],
                null,
                ['PGPASSWORD' => $password],
                null,
                600.0,
            );

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
}
