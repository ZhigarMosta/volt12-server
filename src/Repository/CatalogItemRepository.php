<?php

namespace App\Repository;

use App\Entity\CatalogItemCharacteristic;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;

class CatalogItemRepository extends EntityRepository
{
    public function list(array $productCodes, int $catalogId, array $characteristicIds)
    {
        $qb = $this->createQueryBuilder('ci')
            ->select('ci')
            ->innerJoin('ci.characteristics', 'cic')
            ->where('ci.catalog = :catalogId')
            ->andWhere('ci.product_code IN (:productCodes)')
            ->setParameter('catalogId', $catalogId)
            ->setParameter('productCodes', $productCodes);

        if (!empty($characteristicIds)) {
            $count = count($characteristicIds);

            $qb->andWhere('cic.catalogCharacteristic IN (:characteristicIds)')
                ->setParameter('characteristicIds', $characteristicIds)
                ->groupBy('ci.id')
                ->having('COUNT(DISTINCT cic.catalogCharacteristic) = :count')
                ->setParameter('count', $count);
        }

        return $qb->getQuery()->getResult();
    }
}
