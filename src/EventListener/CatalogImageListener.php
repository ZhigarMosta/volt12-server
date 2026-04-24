<?php

namespace App\EventListener;

use App\Entity\Catalog;
use App\Service\ImageUploader;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Exception;

class CatalogImageListener
{
    public function __construct(
        private ImageUploader $uploader,
    ) {}

    public function prePersist(Catalog $item, $event = null): void
    {
        $this->uploadFile($item, $event);
    }

    public function preUpdate(Catalog $item, $event = null): void
    {
        $this->uploadFile($item, $event);
    }

    private function uploadFile(Catalog $item, $event = null): void
    {
        $file = $item->getFile();

        if (!$file instanceof UploadedFile) {
            return;
        }

        $tempPath = $file->getRealPath();
        if (!file_exists($tempPath)) {
            throw new Exception('Временный файл не найден');
        }

        if ($file->getMimeType() !== 'image/webp') {
            throw new Exception('Допускаются только изображения формата WebP');
        }

        $safePath = sys_get_temp_dir() . '/catalog_upload_' . uniqid() . '.webp';
        copy($tempPath, $safePath);

        try {
            $filename = $this->uploader->upload($safePath);
            $item->setImgLink('uploads/items/' . $filename);

            if ($event instanceof PreUpdateEventArgs) {
                $em = $event->getObjectManager();
                $uow = $em->getUnitOfWork();
                $meta = $em->getClassMetadata(get_class($item));
                $uow->recomputeSingleEntityChangeSet($meta, $item);
            }
        } finally {
            if (file_exists($safePath)) {
                unlink($safePath);
            }
        }
    }
}
