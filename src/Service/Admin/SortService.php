<?php

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;

class SortService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function sort(string $entityClass, array $data): null|int
    {
        $items = $data['items'] ?? [];
        $current = $data['current'] ?? null;

        if (empty($items)) {
            return null;
        }

        $ids = array_column($items, 'id');
        $entities = $this->em->getRepository($entityClass)->findBy(['id' => $ids]);

        $map = [];
        foreach ($entities as $entity) {
            $map[$entity->getId()] = $entity;
        }

        foreach ($items as $itemData) {
            if (!isset($itemData['id'], $itemData['position'], $map[$itemData['id']])) {
                continue;
            }
            $map[$itemData['id']]->setPosition($itemData['position']);
        }

        $this->em->flush();

        if ($current === -1) {
            return $items[array_search($current, array_column($items, 'id'))]['position'] ?? null;
        }

        if (!$current) {
            return null;
        }

        return $map[$current]->getPosition() ?? null;
    }
}
