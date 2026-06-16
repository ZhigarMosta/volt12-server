<?php

namespace App\Repository;

use App\Entity\CatalogGroup;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CatalogGroupRepository extends EntityRepository
{
    /**
     * Получить все группы
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('cg')
            ->orderBy('cg.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить группы для сортировки по ID каталога
     */
    public function findForSortByCatalog(int $catalogId): array
    {
        return $this->createQueryBuilder('cg')
            ->where('cg.catalog = :catalogId')
            ->setParameter('catalogId', $catalogId)
            ->orderBy('cg.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить группу по ID
     */
    public function findOneById(int $id): ?CatalogGroup
    {
        return $this->find($id);
    }
}