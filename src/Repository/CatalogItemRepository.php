<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\Catalog;
use App\Entity\CatalogItem;
use App\Entity\Compare;
use App\Entity\Favorite;
use App\Provider\ProductCodeProvider;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CatalogItemRepository extends EntityRepository
{
    public function findBySlug(string $slug, array $productCodes, ?int $userId = null): ?CatalogItem
    {
        $uid = $userId ?? 0;

        $row = $this->createQueryBuilder('ci')
            ->select('ci')
            ->addSelect('cart.count AS cart_count')
            ->addSelect('cmp.id AS in_compare')
            ->addSelect('fav.id AS in_favorite')
            ->leftJoin(Cart::class, 'cart', 'WITH', 'cart.catalogItem = ci AND cart.user = :uid')
            ->leftJoin(Compare::class, 'cmp', 'WITH', 'cmp.catalogItem = ci AND cmp.user = :uid')
            ->leftJoin(Favorite::class, 'fav', 'WITH', 'fav.catalogItem = ci AND fav.user = :uid')
            ->where('ci.slug = :slug')
            ->andWhere('ci.product_code IN (:productCodes)')
            ->setParameter('slug', $slug)
            ->setParameter('productCodes', $productCodes)
            ->setParameter('uid', $uid)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$row) {
            return null;
        }

        /** @var CatalogItem $item */
        $item = $row[0];
        $item->setCartCount($row['cart_count'] !== null ? (int)$row['cart_count'] : null);
        $item->setInCompare($row['in_compare']);
        $item->setInFavorite($row['in_favorite']);

        return $item;
    }

    public function findRelatedByName(string $name, int $excludeId, array $productCodes, int $limit = 4): array
    {
        $keywords = preg_split('/[\s,.-]+/u', $name, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($keywords)) {
            return [];
        }

        $qb = $this->createQueryBuilder('ci')
            ->where('ci.id != :excludeId')
            ->andWhere('ci.product_code IN (:productCodes)')
            ->setParameter('excludeId', $excludeId)
            ->setParameter('productCodes', $productCodes);

        $conditions = [];
        foreach ($keywords as $i => $keyword) {
            if (mb_strlen($keyword) < 2) {
                continue;
            }
            $conditions[] = "ci.name LIKE :keyword{$i}";
            $qb->setParameter("keyword{$i}", '%' . $keyword . '%');
        }

        if (empty($conditions)) {
            return [];
        }

        $qb->andWhere($qb->expr()->orX(...$conditions))
            ->orderBy('ci.position', 'ASC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findByIds(array $ids, array $productCodes): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('ci')
            ->where('ci.id IN (:ids)')
            ->andWhere('ci.product_code IN (:productCodes)')
            ->setParameter('ids', $ids)
            ->setParameter('productCodes', $productCodes)
            ->orderBy('ci.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function list(array $productCodes, int $catalogId, ?array $filterGroups, ?array $price, ?string $search, ?string $sortPrice, ?int $page, ?int $limit, ?int $userId = null): array
    {
        $uid = $userId ?? 0;

        $qb = $this->createQueryBuilder('ci')
            ->select('ci')
            ->addSelect('cart.count AS cart_count')
            ->addSelect('cmp.id AS in_compare')
            ->addSelect('fav.id AS in_favorite')
            ->leftJoin(Cart::class, 'cart', 'WITH', 'cart.catalogItem = ci AND cart.user = :uid')
            ->leftJoin(Compare::class, 'cmp', 'WITH', 'cmp.catalogItem = ci AND cmp.user = :uid')
            ->leftJoin(Favorite::class, 'fav', 'WITH', 'fav.catalogItem = ci AND fav.user = :uid')
            ->where('ci.catalog = :catalogId')
            ->andWhere('ci.product_code IN (:productCodes)')
            ->setParameter('catalogId', $catalogId)
            ->setParameter('productCodes', $productCodes)
            ->setParameter('uid', $uid);

        if (isset($price['max']) && is_int((int) $price['max'])) {
            $qb->andWhere('ci.price <= (:priceMax)')
                ->setParameter('priceMax', $price['max']);
        }

        if (isset($price['min']) && is_int((int) $price['min'])) {
            $qb->andWhere('ci.price >= (:priceMin)')
                ->setParameter('priceMin', $price['min']);
        }

        if (!empty($search)) {
            $qb->andWhere('ci.name LIKE :searchName')
                ->setParameter('searchName', '%' . $search . '%');
        }

        if (!empty($filterGroups)) {
            foreach ($filterGroups as $index => $ids) {
                $alias = 'cic_' . $index;
                $qb->innerJoin('ci.characteristics', $alias);
                $qb->andWhere($alias . '.catalogCharacteristic IN (:ids_' . $index . ')');
                $qb->setParameter('ids_' . $index, $ids);
            }
        }
        $qb->orderBy('ci.position', 'ASC');
        if ($sortPrice) {
            $qb->orderBy('ci.price', $sortPrice);
        }

        if (!empty($limit) && !empty($page)) {
            $qb->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
            $paginator = new Paginator($qb, true);
            $total = count($paginator);
            $rows = iterator_to_array($paginator);
        } else {
            $rows = $qb->getQuery()->getResult();
            $total = count($rows);
        }

        $items = [];
        foreach ($rows as $row) {
            /** @var CatalogItem $item */
            $item = $row[0];
            $item->setCartCount($row['cart_count'] !== null ? (int)$row['cart_count'] : null);
            $item->setInCompare($row['in_compare']);
            $item->setInFavorite($row['in_favorite']);
            $items[] = $item;
        }

        return ['items' => $items, 'total' => $total];
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
        array $price,
        string $search,
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

        if (isset($price['max']) && is_int((int) $price['max'])) {
            $qb->andWhere('ci.price <= (:priceMax)')
                ->setParameter('priceMax', $price['max']);
        }

        if (isset($price['min']) && is_int((int) $price['min'])) {
            $qb->andWhere('ci.price >= (:priceMin)')
                ->setParameter('priceMin', $price['min']);
        }

        if (!empty($search)) {
            $qb->andWhere('ci.name LIKE :searchName')
                ->setParameter('searchName', '%' . $search . '%');
        }

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
