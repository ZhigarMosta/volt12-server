<?php

namespace App\Repository;

use App\Entity\User;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class FavoriteRepository extends EntityRepository
{
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->addSelect('ci', 'img')
            ->innerJoin('f.catalogItem', 'ci')
            ->leftJoin('ci.catalogItemImages', 'img')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('f.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByIdsForUser(User $user, array $ids): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.user = :user')
            ->andWhere('f.id IN (:ids)')
            ->setParameter('user', $user)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}
