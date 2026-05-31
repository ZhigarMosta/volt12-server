<?php

namespace App\Repository;

use App\Entity\User;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CartRepository extends EntityRepository
{
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->addSelect('ci', 'img')
            ->innerJoin('c.catalogItem', 'ci')
            ->leftJoin('ci.catalogItemImages', 'img')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByIdsForUser(User $user, array $ids): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user')
            ->andWhere('c.id IN (:ids)')
            ->setParameter('user', $user)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}
