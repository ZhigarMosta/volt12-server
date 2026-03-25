<?php

namespace App\Repository;

use App\Entity\Catalog;
use App\Entity\CatalogItem;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CatalogItemRepository extends EntityRepository
{
    public function list(array $productCodes, int $catalogId, array $filterGroups, int $page, int $limit): Paginator
    {
        $qb = $this->createQueryBuilder('ci')
            ->select('ci')
            ->where('ci.catalog = :catalogId')
            ->andWhere('ci.product_code IN (:productCodes)')
            ->setParameter('catalogId', $catalogId)
            ->setParameter('productCodes', $productCodes);

        if (!empty($filterGroups)) {
            foreach ($filterGroups as $index => $ids) {
                $alias = 'cic_' . $index;
                $qb->innerJoin('ci.characteristics', $alias);
                $qb->andWhere($alias . '.catalogCharacteristic IN (:ids_' . $index . ')');
                $qb->setParameter('ids_' . $index, $ids);
            }
        }
        $qb->orderBy('ci.position', 'ASC');
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
    /**
     * Универсальный метод для подсчета фасетов.
     *
     * @param int $catalogId
     * @param array $productCodes
     * @param array $filterGroups Активные фильтры.
     * @param int|null $excludeGroupIndex Индекс фильтра в массиве $filterGroups, который нужно ИГНОРИРОВАТЬ (для логики ИЛИ внутри группы).
     * @param array|null $targetCharIds Оптимизация: считать количество только для этих характеристик.
     * @return array
     */
    public function getFacetCounts(
        int $catalogId,
        array $productCodes,
        array $filterGroups,
        ?int $excludeGroupIndex = null,
        ?array $targetCharIds = null
    ): array
    {
        $qb = $this->createQueryBuilder('ci')
            ->select('IDENTITY(cc.catalogCharacteristic) as char_id', 'COUNT(DISTINCT ci.id) as item_count')
            ->innerJoin('ci.characteristics', 'cc')
            ->where('ci.catalog = :catalogId')
            ->andWhere('ci.product_code IN (:productCodes)')
            ->setParameter('catalogId', $catalogId)
            ->setParameter('productCodes', $productCodes);

        if (!empty($filterGroups)) {
            foreach ($filterGroups as $index => $ids) {
                // Это позволяет получить выборку товаров, игнорируя текущую группу (например, цвет),
                if ($index === $excludeGroupIndex) {
                    continue;
                }

                $alias = 'cic_filter_' . $index;
                $qb->innerJoin('ci.characteristics', $alias);
                $qb->andWhere($alias . '.catalogCharacteristic IN (:ids_' . $index . ')');
                $qb->setParameter('ids_' . $index, $ids);
            }
        }

        // Оптимизация: ограничиваем подсчет только нужными характеристиками
        if (!empty($targetCharIds)) {
            $qb->andWhere('cc.catalogCharacteristic IN (:targetCharIds)')
                ->setParameter('targetCharIds', $targetCharIds);
        }

        $qb->groupBy('char_id');

        return $qb->getQuery()->getResult();
    }
}
