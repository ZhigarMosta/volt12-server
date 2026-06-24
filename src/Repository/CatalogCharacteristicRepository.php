<?php

namespace App\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CatalogCharacteristicRepository extends EntityRepository
{
    public function findByGroup(int $groupId): array
    {
        return $this->createQueryBuilder('cch')
            ->where('cch.catalogGroup = :groupId')
            ->setParameter('groupId', $groupId)
            ->orderBy('cch.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCatalogWithoutGroup(int $catalogId): array
    {
        return $this->createQueryBuilder('cch')
            ->where('cch.catalog = :catalogId')
            ->andWhere('cch.catalogGroup IS NULL')
            ->setParameter('catalogId', $catalogId)
            ->orderBy('cch.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function list(int $catalogId, array $productCodes)
    {
        return $this->createQueryBuilder('cch')
            ->select('cch.id', 'cch.name', 'g.name as group_name', 'g.id as group_id')
            ->leftJoin('cch.catalogGroup', 'g')
            ->where('cch.catalog = :catalogId')
            ->andWhere('cch.product_code IN (:productCodes)')
            ->setParameter('catalogId', $catalogId)
            ->setParameter('productCodes', $productCodes)
            ->orderBy('g.position', 'ASC')
            ->addOrderBy('cch.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
