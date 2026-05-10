<?php

namespace App\Service\Volt12;

use App\Entity\CatalogItemImage;
use Doctrine\ORM\EntityManagerInterface;

class CatalogItemImageService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function sortCatalogItemImage(array $data): int
    {
        $items = $data['items'] ?? [];

        foreach ($items as $item) {
            $image = $this->em->getRepository(CatalogItemImage::class)->find($item['id']);
            if ($image) {
                $image->setPosition($item['position']);
            }
        }

        $this->em->flush();

        return $data['current'] ?? 0;
    }
}