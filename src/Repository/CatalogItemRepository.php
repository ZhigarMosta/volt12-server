<?php

namespace App\Repository;

use App\Entity\Catalog;
use App\Entity\CatalogItem;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CatalogItemRepository extends EntityRepository
{
    public function list(array $productCodes, int $catalogId, array $characteristicIds, int $page, int $limit): Paginator
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
        $qb->orderBy('ci.position', 'DESC');
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        return new Paginator($qb, true);
    }

    public function findPopular(array $productCodes)
    {
        return $this->createQueryBuilder('ci')
            ->select('ci')
            ->where('ci.product_code IN (:productCodes)')
            ->andWhere('ci.is_popular = :isPopular')
            ->setParameter('productCodes', $productCodes)
            ->setParameter('isPopular', CatalogItem::POPULAR)
            ->orderBy('ci.position', 'ASC')
            ->setMaxResults(CatalogItem::LIMIT_POPULAR)
            ->getQuery()
            ->getResult();
    }

    public function findPopularByFirstPopularCatalog(array $productCodes, $catalog)
    {
        return $this->createQueryBuilder('ci')
            ->select('ci')
            ->where('ci.product_code IN (:productCodes)')
            ->andWhere('ci.is_popular = :isPopular')
            ->andWhere('ci.catalog = :catalog')
            ->setParameter('productCodes', $productCodes)
            ->setParameter('isPopular', CatalogItem::POPULAR)
            ->setParameter('catalog', $catalog)
            ->orderBy('ci.position', 'ASC')
            ->setMaxResults(CatalogItem::LIMIT_POPULAR)
            ->getQuery()
            ->getResult();
    }
}
