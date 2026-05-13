<?php

namespace App\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ServiceRepository extends EntityRepository
{
    public function list(?int $serviceGroupId, ?string $search, int $page = 1, int $limit = 10): Paginator
    {
        $qb = $this->createQueryBuilder('s')
            ->select('s')
            ->orderBy('s.position', 'ASC');

        if ($serviceGroupId !== null) {
            $qb->andWhere('s.serviceGroup = :serviceGroupId')
                ->setParameter('serviceGroupId', $serviceGroupId);
        }

        if (!empty($search)) {
            $qb->andWhere('s.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb, true);
    }
}
