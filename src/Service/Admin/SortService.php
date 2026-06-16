<?php

namespace App\Service\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;

class SortService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?LoggerInterface $logger = null,
    ) {}

    /**
     * Универсальный метод сортировки сущностей
     * 
     * @param array $data Данные сортировки ['items' => [...], 'current' => ...]
     * @param string $entityClass Класс сущности
     * @param string $positionField Название поля позиции (по умолчанию 'position')
     * @return array|null Результат сортировки
     */
    public function sort(array $data, string $entityClass, string $positionField = 'position'): ?array
    {
        $items = $data['items'] ?? [];
        $current = $data['current'] ?? null;

        if (empty($items)) {
            return null;
        }

        $ids = array_column($items, 'id');
        
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository($entityClass);
        $entities = $repository->findBy(['id' => $ids]);

        $entityMap = [];
        foreach ($entities as $entity) {
            $entityMap[$entity->getId()] = $entity;
        }

        foreach ($items as $itemData) {
            if (!isset($itemData['id']) || !isset($itemData['position'])) {
                continue;
            }

            if (!isset($entityMap[$itemData['id']])) {
                continue;
            }

            $setter = 'set' . ucfirst($positionField);
            if (method_exists($entityMap[$itemData['id']], $setter)) {
                $entityMap[$itemData['id']]->$setter($itemData['position']);
                $this->entityManager->persist($entityMap[$itemData['id']]);
            }
        }

        $this->entityManager->flush();

        // Возвращаем позицию текущего элемента
        if ($current === -1) {
            $currentIndex = array_search($current, array_column($items, 'id'));
            return [
                'position' => $items[$currentIndex]['position'] ?? null,
                'success' => true
            ];
        }
        
        if ($current && isset($entityMap[$current])) {
            $getter = 'get' . ucfirst($positionField);
            if (method_exists($entityMap[$current], $getter)) {
                return [
                    'position' => $entityMap[$current]->$getter(),
                    'success' => true
                ];
            }
        }
        
        return ['success' => true];
    }

    /**
     * Получение сущностей для сортировки по родительскому ID
     * 
     * @param string $entityClass Класс сущности
     * @param string $parentField Поле связи с родителем
     * @param int $parentId ID родительской сущности
     * @param string $sortField Поле сортировки (по умолчанию 'position')
     * @param string $sortOrder Направление сортировки
     * @param callable $transformCallback Функция трансформации сущности в массив
     * @return array
     */
    public function getEntitiesForSort(
        string $entityClass,
        string $parentField,
        int $parentId,
        string $sortField = 'position',
        string $sortOrder = 'ASC',
        callable $transformCallback
    ): array {
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository($entityClass);
        
        $entities = $repository->findBy(
            [$parentField => $parentId],
            [$sortField => $sortOrder]
        );

        return array_map($transformCallback, $entities);
    }
}