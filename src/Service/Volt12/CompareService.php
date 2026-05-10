<?php

namespace App\Service\Volt12;

use App\Entity\Compare;
use App\Entity\CatalogItem;
use App\Entity\User;
use App\Repository\CompareRepository;
use Doctrine\ORM\EntityManagerInterface;

class CompareService
{
    public function __construct(
        private CompareRepository $compareRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function list(User $user): array
    {
        return array_map(
            fn(Compare $item) => $this->serialize($item),
            $this->compareRepository->findBy(['user' => $user])
        );
    }

    public function add(User $user, CatalogItem $catalogItem): array
    {
        $existing = $this->compareRepository->findOneBy([
            'user' => $user,
            'catalogItem' => $catalogItem,
        ]);

        if ($existing) {
            return $this->serialize($existing);
        }

        $compare = new Compare();
        $compare->setUser($user);
        $compare->setCatalogItem($catalogItem);

        $this->entityManager->persist($compare);
        $this->entityManager->flush();

        return $this->serialize($compare);
    }

    public function remove(User $user, int $id): bool
    {
        $item = $this->compareRepository->findOneBy(['id' => $id, 'user' => $user]);
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

    private function serialize(Compare $compare): array
    {
        return [
            'id' => $compare->getId(),
            'catalog_item_id' => $compare->getCatalogItem()?->getId(),
            'created_at' => $compare->getCreatedAt()?->format('c'),
        ];
    }
}
