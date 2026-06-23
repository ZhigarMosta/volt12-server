<?php

namespace App\Twig;

use App\Repository\EntityHistoryRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class EntityHistoryExtension extends AbstractExtension
{
    public function __construct(
        private EntityHistoryRepository $repository,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('entity_has_history', [$this, 'entityHasHistory']),
            new TwigFunction('get_entity_history', [$this, 'getEntityHistory']),
            new TwigFunction('get_entity_history_count', [$this, 'getEntityHistoryCount']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('entity_type', [$this, 'resolveEntityType']),
        ];
    }

    public function entityHasHistory(object $entity): bool
    {
        return method_exists($entity, 'hasHistory') && $entity->hasHistory();
    }

    public function getEntityHistory(object $entity, int $page = 1): array
    {
        if (!$this->entityHasHistory($entity)) {
            return [];
        }

        if (!method_exists($entity, 'getId') || $entity->getId() === null) {
            return [];
        }

        $entityType = $this->resolveEntityType(get_class($entity));

        return $this->repository->findForEntity($entityType, (int) $entity->getId(), max(1, $page));
    }

    public function getEntityHistoryCount(object $entity): int
    {
        if (!$this->entityHasHistory($entity)) {
            return 0;
        }

        if (!method_exists($entity, 'getId') || $entity->getId() === null) {
            return 0;
        }

        $entityType = $this->resolveEntityType(get_class($entity));

        return $this->repository->countForEntity($entityType, (int) $entity->getId());
    }

    public function resolveEntityType(string $entityClass): string
    {
        $shortName = (new \ReflectionClass($entityClass))->getShortName();
        return mb_strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName));
    }
}
