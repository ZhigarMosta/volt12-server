<?php

namespace App\Repository;

use App\Entity\Catalog;
use Doctrine\ORM\AbstractQuery;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CatalogRepository extends EntityRepository
{
    public function list(string $code): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.name')
            ->where('c.product_code = :product_code')
            ->setParameter('product_code', $code)
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
