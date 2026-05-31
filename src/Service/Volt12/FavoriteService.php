<?php

namespace App\Service\Volt12;

use App\Entity\CatalogItem;
use App\Entity\Favorite;
use App\Entity\User;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;

class FavoriteService
{
    public function __construct(
        private FavoriteRepository $favoriteRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function list(User $user): array
    {
        return array_map(
            fn(Favorite $item) => $this->serialize($item),
            $this->favoriteRepository->findByUser($user)
        );
    }

    public function add(User $user, CatalogItem $catalogItem): array
    {
        $existing = $this->favoriteRepository->findOneBy([
            'user' => $user,
            'catalogItem' => $catalogItem,
        ]);

        if ($existing) {
            return $this->serialize($existing);
        }

        $favorite = new Favorite();
        $favorite->setUser($user);
        $favorite->setCatalogItem($catalogItem);

        $this->entityManager->persist($favorite);
        $this->entityManager->flush();

        return $this->serialize($favorite);
    }

    public function remove(User $user, int $id): bool
    {
        $item = $this->favoriteRepository->findOneBy(['id' => $id, 'user' => $user]);
        if (!$item) return false;

        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return true;
    }

    public function removeMany(User $user, array $ids): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return 0;
        }

        $items = $this->favoriteRepository->findByIdsForUser($user, $ids);
        foreach ($items as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->flush();

        return count($items);
    }

    private function serialize(Favorite $favorite): array
    {
        $catalogItem = $favorite->getCatalogItem();

        return [
            'id' => $favorite->getId(),
            'catalog_item' => $catalogItem ? $this->serializeCatalogItem($catalogItem) : null,
            'created_at' => $favorite->getCreatedAt()?->format('c'),
        ];
    }

    private function serializeCatalogItem(CatalogItem $item): array
    {
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

        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'slug' => $item->getSlug(),
            'price' => $item->getPrice(),
            'images' => $images,
        ];
    }
}
