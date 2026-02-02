<?php

namespace App\EventListener;

use App\Entity\CatalogItem;
use App\Service\ImageUploader;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Exception;
use Symfony\Component\Yaml\Dumper;

class CatalogItemListener
{

    public function __construct(
        private ImageUploader $uploader
    ) {}

    public function prePersist(CatalogItem $item, PrePersistEventArgs $event): void
    {
        $this->uploadFile($item, $event);
        $this->checkPosition($item, $event->getObjectManager());
    }

    public function preUpdate(CatalogItem $item, PreUpdateEventArgs $event): void
    {
        var_dump($item->getFile());
//        die();
        $this->uploadFile($item, $event);
        $this->checkPosition($item, $event->getObjectManager());
    }

    private function uploadFile(CatalogItem $item, EventArgs $event = null): void
    {
        $file = $item->getFile();

        if (!$file instanceof UploadedFile) {
            return;
        }

        if ($file->getMimeType() !== 'image/webp') {
            throw new Exception('Допускаются только изображения формата WebP');
        }

        // 1. Загружаем файл на диск
        $filename = $this->uploader->upload($file);
        $newPath = 'uploads/items/' . $filename;

        // 2. Устанавливаем путь в объект
        $item->setImgLink($newPath);

        // 3. ОБНОВЛЕНИЕ ChangeSet
        if ($event instanceof PreUpdateEventArgs) {
            // Удаляем setNewValue, он здесь лишний и может работать неправильно с 3 аргументами.

            // Используем ТОЛЬКО этот метод. Он принудительно скажет Doctrine:
            // "Пересмотри эту сущность, в ней что-то изменилось (наш img_link)"
            $em = $event->getObjectManager();
            $uow = $em->getUnitOfWork();
            $meta = $em->getClassMetadata(get_class($item));

            // Пересчитываем изменения
            $uow->recomputeSingleEntityChangeSet($meta, $item);
        }
    }


    private function checkPosition(CatalogItem $item, ObjectManager $entityManager): void
    {
        $position = $item->getPosition();

        if ($position === null) {
            return;
        }

        $repository = $entityManager->getRepository(CatalogItem::class);

        $existing = $repository->findOneBy(['position' => $position]);

        if ($existing && $existing->getId() !== $item->getId()) {
            throw new Exception(
                sprintf("Такой номер уже есть у продукта '%s'", $existing->getName())
            );
        }
    }
}
