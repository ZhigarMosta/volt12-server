<?php

namespace App\Service\Volt12;

use App\Entity\CatalogItem;
use App\Entity\User;
use App\Repository\CatalogItemRepository;
use App\Repository\CompareRepository;
use Doctrine\ORM\EntityManagerInterface;

class CompareService
{
    public function __construct(
        private CompareRepository $compareRepository,
        private CatalogItemRepository $catalogItemRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function list(User $user): array
    {
        $catalogItemIds = $this->compareRepository->findCatalogItemIdsByUser($user);

        if (empty($catalogItemIds)) {
            return [];
        }

        return $this->buildGrouped($this->catalogItemRepository->findBy(['id' => $catalogItemIds]));
    }

    private function buildGrouped(array $items): array
    {
        $grouped = [];
        /** @var CatalogItem $item */
        foreach ($items as $item) {
            $catalog = $item->getCatalog();
            $catalogId = $catalog?->getId();

            if ($catalogId === null) {
                continue;
            }

            if (!isset($grouped[$catalogId])) {
                $chars = [];
                foreach ($catalog->getCharacteristics() as $char) {
                    $chars[] = [
                        'id' => $char->getId(),
                        'name' => $char->getName(),
                        'group_id' => $char->getCatalogGroup()?->getId(),
                        'group_name' => $char->getCatalogGroup()?->getName(),
                    ];
                }

                $grouped[$catalogId] = [
                    'catalog' => [
                        'id' => $catalog->getId(),
                        'name' => $catalog->getName(),
                        'img' => [
                            'link' => $catalog->getImgLink(),
                            'alt' => $catalog->getImgAlt(),
                            'title' => $catalog->getImgTitle(),
                        ],
                        'characteristics' => $chars,
                    ],
                    'items' => [],
                ];
            }

            $images = [];
            foreach ($item->getCatalogItemImages() as $image) {
                $images[] = [
                    'id' => $image->getId(),
                    'img_link' => $image->getImgLink(),
                    'alt' => $image->getAlt(),
                    'title' => $image->getTitle(),
                    'position' => $image->getPosition(),
                ];
            }

            $characteristics = [];
            foreach ($item->getCharacteristics() as $cic) {
                $cc = $cic->getCatalogCharacteristic();
                if ($cc) {
                    $characteristics[] = [
                        'characteristic_id' => $cc->getId(),
                        'characteristic_name' => $cc->getName(),
                        'group_id' => $cc->getCatalogGroup()?->getId(),
                        'group_name' => $cc->getCatalogGroup()?->getName(),
                    ];
                }
            }

            $grouped[$catalogId]['items'][] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'slug' => $item->getSlug(),
                'price' => $item->getPrice(),
                'images' => $images,
                'characteristics' => $characteristics,
            ];
        }

        return array_values($grouped);
    }

    public function listByIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return [];
        }

        $items = $this->catalogItemRepository->findBy(['id' => $ids]);

        return $this->buildGrouped($items);
    }

    public function add(User $user, CatalogItem $catalogItem): array
    {
        $existing = $this->compareRepository->findOneBy([
            'user' => $user,
            'catalogItem' => $catalogItem,
        ]);

        if ($existing) {
            return ['id' => $existing->getId(), 'catalog_item_id' => $catalogItem->getId()];
        }

        $compare = new \App\Entity\Compare();
        $compare->setUser($user);
        $compare->setCatalogItem($catalogItem);

        $this->entityManager->persist($compare);
        $this->entityManager->flush();

        return ['id' => $compare->getId(), 'catalog_item_id' => $catalogItem->getId()];
    }

    public function remove(User $user, int $catalogItemId): bool
    {
        $item = $this->compareRepository->findOneBy(['catalogItem' => $catalogItemId, 'user' => $user]);
        if (!$item) return false;

        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return true;
    }

    public function clear(User $user): void
    {
        $items = $this->compareRepository->findBy(['user' => $user]);
        foreach ($items as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->flush();
    }
}
