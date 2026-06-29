<?php

namespace App\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Service;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ServiceRepository extends EntityRepository
{
    public function findBySlug(string $slug): ?Service
    {
        return $this->createQueryBuilder('s')
            ->where('s.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findRelatedByName(string $name, int $excludeId, int $limit = 4): array
    {
        $keywords = preg_split('/[\s,.-]+/u', $name, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($keywords)) {
            return [];
        }

        $qb = $this->createQueryBuilder('s')
            ->where('s.id != :excludeId')
            ->setParameter('excludeId', $excludeId);

        $conditions = [];
        foreach ($keywords as $i => $keyword) {
            if (mb_strlen($keyword) < 2) {
                continue;
            }
            $conditions[] = "s.name LIKE :keyword{$i}";
            $qb->setParameter("keyword{$i}", '%' . $keyword . '%');
        }

        if (empty($conditions)) {
            return [];
        }

        $qb->andWhere($qb->expr()->orX(...$conditions))
            ->orderBy('s.position', 'ASC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

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

        $qb->andWhere('s.img_link IS NOT NULL')
            ->andWhere('s.img_link != :empty')
            ->setParameter('empty', '')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb, true);
    }

    public function findTopForMenu(int $limit): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchByName(string $name, int $limit): array
    {
        return $this->createQueryBuilder('s')
            ->where('LOWER(s.name) LIKE LOWER(:name)')
            ->andWhere('s.img_link IS NOT NULL')
            ->andWhere('s.img_link != :empty')
            ->setParameter('empty', '')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
