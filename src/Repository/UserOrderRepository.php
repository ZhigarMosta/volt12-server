<?php

namespace App\Repository;

use App\Entity\UserOrder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class UserOrderRepository extends EntityRepository
{
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPageByUser(int $userId, int $page, int $perPage): array
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('o.createdAt', 'DESC');

        $total = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->where('o.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'total' => (int) $total,
            'page'  => $page,
            'per_page' => $perPage,
            'pages' => (int) ceil($total / $perPage),
        ];
    }
}
