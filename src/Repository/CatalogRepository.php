<?php

namespace App\Repository;

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
}
