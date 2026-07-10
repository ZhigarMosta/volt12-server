<?php

namespace App\Controller\Admin;

use App\Service\DatabaseRestoreService;
use App\Service\ImageIntegrityService;
use App\Service\ImagesBackupService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/tools')]
class DatabaseRestoreController extends AbstractController
{
    public function __construct(
        private DatabaseRestoreService $restoreService,
        private ImagesBackupService $imagesBackupService,
        private ImageIntegrityService $imageIntegrityService,
    ) {}

    /**
     * Шаг 1: проверка дампа без выполнения — отчёт валидации + кнопка подтверждения.
     */
    #[Route(
        '/db-restore/check/{kind}/{stamp}',
        name: 'app_admin_db_restore_check',
        requirements: ['kind' => 'dump|upload', 'stamp' => '\d{8}-\d{6}'],
        methods: ['GET'],
    )]
    public function check(string $kind, string $stamp): Response
    {
        $path = $this->restoreService->resolveDumpPath($kind, $stamp);
        if ($path === null) {
            throw $this->createNotFoundException('Дамп не найден.');
        }

        $validation = $this->restoreService->validateDumpFile($path);

        return $this->render('admin/tools/restore_check.html.twig', [
            'kind' => $kind,
            'stamp' => $stamp,
            'file' => basename($path),
            'mtime' => (int) filemtime($path),
            'validation' => $validation,
        ]);
    }

    /**
     * Шаг 2: собственно восстановление (после проверки и явного подтверждения).
     */
    #[Route(
        '/db-restore/run/{kind}/{stamp}',
        name: 'app_admin_db_restore_run',
        requirements: ['kind' => 'dump|upload', 'stamp' => '\d{8}-\d{6}'],
        methods: ['POST'],
    )]
    public function run(Request $request, string $kind, string $stamp): Response
    {
        if (!$this->isCsrfTokenValid('db-restore-run', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Неверный CSRF-токен.');
        }
        if ($request->request->get('confirm') !== '1') {
            $this->addFlash('error', 'Восстановление не подтверждено.');

            return $this->redirectToRoute('app_admin_db_restore_check', ['kind' => $kind, 'stamp' => $stamp]);
        }

        $path = $this->restoreService->resolveDumpPath($kind, $stamp);
        if ($path === null) {
            throw $this->createNotFoundException('Дамп не найден.');
        }

        try {
            $report = $this->restoreService->restore($path);
        } catch (\Throwable $e) {
            return $this->render('admin/tools/restore_result.html.twig', [
                'success' => false,
                'file' => basename($path),
                'error' => $e->getMessage(),
                'report' => null,
                'missingImages' => [],
                'totalImages' => 0,
            ]);
        }

        // Дамп не содержит файлов картинок — сразу показываем, каких не хватает
        $missingImages = $this->imageIntegrityService->findMissingImages();

        return $this->render('admin/tools/restore_result.html.twig', [
            'success' => true,
            'file' => basename($path),
            'error' => null,
            'report' => $report,
            'missingImages' => $missingImages,
            'totalImages' => $this->imageIntegrityService->countImageRecords(),
        ]);
    }

    /**
     * Загрузка своего .sql-дампа: сохраняем и отправляем на страницу проверки.
     */
    #[Route('/db-restore/upload', name: 'app_admin_db_restore_upload', methods: ['POST'])]
    public function upload(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('db-restore-upload', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Неверный CSRF-токен.');
        }

        $file = $request->files->get('dump');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            $this->addFlash('error', 'Файл не загружен (возможно, превышен лимит размера загрузки PHP).');

            return $this->redirectToRoute('sylius_admin_dashboard');
        }

        if (strtolower((string) $file->getClientOriginalExtension()) !== 'sql') {
            $this->addFlash('error', 'Ожидается файл с расширением .sql.');

            return $this->redirectToRoute('sylius_admin_dashboard');
        }

        $stored = $this->restoreService->storeUploadedDump($file->getPathname());

        return $this->redirectToRoute('app_admin_db_restore_check', $stored);
    }

    /**
     * Выгрузка всех картинок сайта (public/uploads) одним zip-архивом.
     */
    #[Route('/images-export', name: 'app_admin_images_export', methods: ['GET'])]
    public function imagesExport(): Response
    {
        $zipPath = $this->imagesBackupService->createArchive();

        $response = new BinaryFileResponse($zipPath);
        $response->deleteFileAfterSend(true);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('images-%s.zip', date('Ymd-His')),
        );

        return $response;
    }

    /**
     * Восстановление картинок из загруженного zip-архива.
     */
    #[Route('/images-restore', name: 'app_admin_images_restore', methods: ['POST'])]
    public function imagesRestore(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('images-restore', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Неверный CSRF-токен.');
        }

        $file = $request->files->get('archive');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            $this->addFlash('error', 'Архив не загружен (возможно, превышен лимит размера загрузки PHP).');

            return $this->redirectToRoute('sylius_admin_dashboard');
        }

        try {
            $report = $this->imagesBackupService->restoreArchive($file->getPathname());
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Восстановление картинок не удалось: ' . $e->getMessage());

            return $this->redirectToRoute('sylius_admin_dashboard');
        }

        return $this->render('admin/tools/images_result.html.twig', [
            'report' => $report,
            'missingImages' => $this->imageIntegrityService->findMissingImages(),
            'totalImages' => $this->imageIntegrityService->countImageRecords(),
        ]);
    }

    /**
     * Отдельная проверка целостности картинок (без восстановления).
     */
    #[Route('/images-check', name: 'app_admin_images_check', methods: ['GET'])]
    public function imagesCheck(): Response
    {
        return $this->render('admin/tools/images_check.html.twig', [
            'missingImages' => $this->imageIntegrityService->findMissingImages(),
            'totalImages' => $this->imageIntegrityService->countImageRecords(),
        ]);
    }
}
