<?php

namespace App\Repository;

use App\Entity\Catalog;
use App\Entity\CatalogGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class CatalogCharacteristicRepository extends EntityRepository
{
    public function list(int $catalogId, array $productCodes)
    {
        return $this->createQueryBuilder('cch')
            ->select('cch.id', 'cch.name', 'gch.name as group_name')
            ->leftJoin(
                Catalog::class,
                'c',
                Join::WITH,
                'cch.catalog = c'
            )
            ->leftJoin(
                CatalogGroup::class,
                'gch',
                Join::WITH,
                'gch.catalogCharacteristic = cch'
            )
            ->where('cch.catalog = :catalogId')
            ->andWhere('c.product_code IN (:productCodes)')
            ->setParameters(new ArrayCollection([
                new Parameter('catalogId', $catalogId),
                new Parameter('productCodes', $productCodes),
            ]))
            ->getQuery()
            ->getResult();
    }
}
