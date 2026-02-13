<?php

namespace App\Repository;

use App\Entity\CatalogItemCharacteristic;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;

class CatalogItemRepository extends EntityRepository
{
    public function list(string $productCode, int $catalogId, array $characteristicId)
    {
        $res = $this->createQueryBuilder('ci')
            ->innerJoin(
                CatalogItemCharacteristic::class,
                'cic',
                Join::WITH,
                'cic.catalogItem = ci'
            )
            ->where('ci.catalog = :catalogId')
            ->andWhere('ci.product_code = :productCode')
            ->setParameters(new ArrayCollection([
                new Parameter('catalogId', $catalogId),
                new Parameter('productCode', $productCode),
            ]));
        if (count($characteristicId)) {
            $res = $res->andWhere('cic.catalogCharacteristic IN (:characteristicId)')
                ->setParameter('characteristicId', $characteristicId);
        }
        return $res->getQuery()
            ->getResult();
    }
}
