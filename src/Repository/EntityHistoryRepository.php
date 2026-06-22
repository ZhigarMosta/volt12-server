<?php

namespace App\Repository;

use App\Entity\EntityHistory;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class EntityHistoryRepository extends EntityRepository
{
    public function findForEntity(string $entityType, int $entityId, int $page = 1, int $perPage = 3): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.entity = :entity')
            ->andWhere('h.entityId = :entityId')
            ->setParameter('entity', $entityType)
            ->setParameter('entityId', $entityId)
            ->orderBy('h.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function countForEntity(string $entityType, int $entityId): int
    {
        return (int) $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where('h.entity = :entity')
            ->andWhere('h.entityId = :entityId')
            ->setParameter('entity', $entityType)
            ->setParameter('entityId', $entityId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
