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
            $this->cartRepository->findBy(['user' => $user])
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

    public function clear(User $user): void
    {
        $items = $this->cartRepository->findBy(['user' => $user]);
        foreach ($items as $item) {
            $this->entityManager->remove($item);
        }
        $this->entityManager->flush();
    }

    private function serialize(Cart $cart): array
    {
        return [
            'id' => $cart->getId(),
            'catalog_item_id' => $cart->getCatalogItem()?->getId(),
            'count' => $cart->getCount(),
            'created_at' => $cart->getCreatedAt()?->format('c'),
        ];
    }
}
