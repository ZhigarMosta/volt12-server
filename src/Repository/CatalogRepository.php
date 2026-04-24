<?php

namespace App\Repository;

use App\Entity\Catalog;
use Doctrine\ORM\AbstractQuery;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CatalogRepository extends EntityRepository
{
    public function list(array $codes): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.name, c.slug, c.img_link, c.imgAlt, c.imgTitle, (
            SELECT COUNT(DISTINCT i2.id)
            FROM App\Entity\CatalogItem i2
            WHERE i2.catalog = c.id
            AND i2.product_code IN (:product_code)
            AND EXISTS (
                SELECT 1
                FROM App\Entity\CatalogItemImage img2
                WHERE img2.catalogItem = i2.id
            )
        ) as items_count')
            ->where('c.product_code IN (:product_code)')
            ->setParameter('product_code', $codes)
            ->getQuery()
            ->getResult();
    }

    public function popularListWithLimit(array $codes)
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.name')
            ->where('c.product_code IN (:productCode)')
            ->andWhere('c.is_popular = :isPopular')
            ->setParameter('productCode', $codes)
            ->setParameter('isPopular', Catalog::POPULAR)
            ->orderBy('c.position')
            ->setMaxResults(Catalog::LIMIT_POPULAR)
            ->getQuery()
            ->getResult();
    }

    public function firstPopular(array $codes)
    {
        return $this->createQueryBuilder('c')
            ->where('c.product_code IN (:productCode)')
            ->andWhere('c.is_popular = :isPopular')
            ->setParameter('productCode', $codes)
            ->setParameter('isPopular', Catalog::POPULAR)
            ->orderBy('c.position')
            ->setMaxResults(Catalog::FIRST_POPULAR)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function byId(int $id): array
    {
        return $this->createQueryBuilder('c')->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}
