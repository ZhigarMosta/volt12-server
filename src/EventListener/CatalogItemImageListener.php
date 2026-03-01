<?php

namespace App\EventListener;

use App\Entity\CatalogItemImage;
use App\Service\ImageUploader;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Exception;

class CatalogItemImageListener
{
    public function __construct(
        private ImageUploader $uploader,
    ) {}

    public function prePersist(CatalogItemImage $item, PrePersistEventArgs $event): void
    {
        $this->uploadFile($item, $event);
    }

    public function preUpdate(CatalogItemImage $item, PreUpdateEventArgs $event): void
    {
        $this->uploadFile($item, $event);
    }

    private function uploadFile(CatalogItemImage $item, EventArgs $event = null): void
    {
        $file = $item->getFile();

        if (!$file instanceof UploadedFile) {
            return;
        }

        if ($file->getMimeType() !== 'image/webp') {
            throw new Exception('Допускаются только изображения формата WebP');
        }

        $filename = $this->uploader->upload($file);
        $newPath = 'uploads/items/' . $filename;

        $item->setImgLink($newPath);

        if ($event instanceof PreUpdateEventArgs) {
            $em = $event->getObjectManager();
            $uow = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($item));

            $uow->recomputeSingleEntityChangeSet($meta, $item);
        }
    }
}
