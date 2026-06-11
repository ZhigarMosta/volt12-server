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
}
