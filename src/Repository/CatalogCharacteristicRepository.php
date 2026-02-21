<?php

namespace App\Repository;

use App\Entity\CatalogGroupCharacteristic;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class CatalogCharacteristicRepository extends EntityRepository
{
    public function list(int $catalogId, array $productCodes)
    {
        return $this->createQueryBuilder('cch')
            ->select('cch.id', 'cch.name', 'g.name as group_name')
            ->leftJoin('cch.catalog', 'c')
            ->leftJoin(
                CatalogGroupCharacteristic::class,
                'cgc',
                Join::WITH,
                'cgc.catalogCharacteristic = cch'
            )
            ->leftJoin('cgc.catalogGroup', 'g')
            ->where('cch.catalog = :catalogId')
            ->andWhere('cch.product_code IN (:productCodes)')
            ->setParameters(new ArrayCollection([
                new Parameter('catalogId', $catalogId),
                new Parameter('productCodes', $productCodes),
            ]))
            ->groupBy('cch.id')
            ->addGroupBy('cch.name')
            ->addGroupBy('g.name')
            ->getQuery()
            ->getResult();
    }
}
