<?php

namespace App\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CatalogCharacteristicRepository extends EntityRepository
{
    public function list(int $catalogId, array $productCodes)
    {
        return $this->createQueryBuilder('cch')
            ->select('cch.id', 'cch.name', 'g.name as group_name')
            ->leftJoin('cch.catalogGroup', 'g')
            ->where('cch.catalog = :catalogId')
            ->andWhere('cch.product_code IN (:productCodes)')
            ->setParameter('catalogId', $catalogId)
            ->setParameter('productCodes', $productCodes)
            ->orderBy('g.name', 'ASC')
            ->addOrderBy('cch.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
