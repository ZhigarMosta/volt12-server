<?php

namespace App\Service\Volt12;

use App\Entity\Cart;
use App\Entity\CatalogItem;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    public function __construct(
        private CartRepository $cartRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function list(User $user): array
    {
        return array_map(
            fn(Cart $item) => $this->serialize($item),
            $this->cartRepository->findByUser($user)
        );
    }

    public function add(User $user, CatalogItem $catalogItem, int $count = 1): array
    {
        $existing = $this->cartRepository->findOneBy([
            'user' => $user,
            'catalogItem' => $catalogItem,
        ]);

        if ($existing) {
            $existing->setCount($existing->getCount() + $count);
            $this->entityManager->flush();
            return $this->serialize($existing);
        }

        $cart = new Cart();
        $cart->setUser($user);
        $cart->setCatalogItem($catalogItem);
        $cart->setCount(max(1, $count));

        $this->entityManager->persist($cart);
        $this->entityManager->flush();

        return $this->serialize($cart);
    }

    public function updateCount(User $user, int $id, int $count): ?array
    {
        $item = $this->cartRepository->findOneBy(['id' => $id, 'user' => $user]);
        if (!$item) return null;

        if ($count <= 0) {
            $this->entityManager->remove($item);
            $this->entityManager->flush();
            return [];
        }

        $item->setCount($count);
        $this->entityManager->flush();

        return $this->serialize($item);
    }

    public function remove(User $user, int $id): bool
    {
        $item = $this->cartRepository->findOneBy(['id' => $id, 'user' => $user]);
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

        $items = $this->cartRepository->findByIdsForUser($user, $ids);
        foreach ($items as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->flush();

        return count($items);
    }

    private function serialize(Cart $cart): array
    {
        $catalogItem = $cart->getCatalogItem();

        return [
            'id' => $cart->getId(),
            'catalog_item' => $catalogItem ? $this->serializeCatalogItem($catalogItem) : null,
            'count' => $cart->getCount(),
            'created_at' => $cart->getCreatedAt()?->format('c'),
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
