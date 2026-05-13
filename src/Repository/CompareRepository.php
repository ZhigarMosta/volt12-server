<?php

namespace App\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CompareRepository extends EntityRepository
{
    public function findCatalogItemIdsByUser($user): array
    {
        return $this->createQueryBuilder('c')
            ->select('IDENTITY(c.catalogItem) as catalogItemId')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
