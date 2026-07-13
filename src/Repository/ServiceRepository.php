<?php

namespace App\Repository;

use App\Provider\ProductCodeProvider;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Service;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ServiceRepository extends EntityRepository
{
    /**
     * Витринные коды: услуги наследуют код продукта своей группы,
     * записи с кодом «Пандора» на этом сайте не показываются.
     */
    private const STOREFRONT_CODES = [ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY];

    private function applyStorefrontCodes(QueryBuilder $qb): QueryBuilder
    {
        return $qb
            ->join('s.serviceGroup', 'sg')
            ->andWhere('sg.product_code IN (:storefrontCodes)')
            ->setParameter('storefrontCodes', self::STOREFRONT_CODES);
    }

    private function applyPublished(QueryBuilder $qb): QueryBuilder
    {
        return $qb->andWhere('s.is_published = true');
    }

    public function findBySlug(string $slug): ?Service
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.slug = :slug')
            ->setParameter('slug', $slug);

        // страница услуги открывается и для неопубликованной (витрина покажет пометку),
        // но услуги «чужого» кода продукта скрыты полностью
        $this->applyStorefrontCodes($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findRelatedByName(string $name, int $excludeId, int $limit = 4): array
    {
        $keywords = preg_split('/[\s,.-]+/u', $name, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($keywords)) {
            return [];
        }

        $qb = $this->createQueryBuilder('s')
            ->where('s.id != :excludeId')
            ->andWhere('s.img_link IS NOT NULL')
            ->andWhere('s.img_link != :empty')
            ->setParameter('empty', '')
            ->setParameter('excludeId', $excludeId);

        $this->applyStorefrontCodes($qb);
        $this->applyPublished($qb);

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

        $this->applyStorefrontCodes($qb);
        $this->applyPublished($qb);

        if ($serviceGroupId !== null) {
            $qb->andWhere('s.serviceGroup = :serviceGroupId')
                ->setParameter('serviceGroupId', $serviceGroupId);
        }

        if (!empty($search)) {
            $qb->andWhere('LOWER(s.name) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->andWhere('s.img_link IS NOT NULL')
            ->andWhere('s.img_link != :empty')
            ->setParameter('empty', '')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb, true);
    }

    public function findFooterServices(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->join('s.serviceGroup', 'g')
            ->where('s.in_footer = true')
            ->andWhere('g.product_code IN (:storefrontCodes)')
            ->setParameter('storefrontCodes', self::STOREFRONT_CODES)
            ->orderBy('g.position', 'ASC')
            ->addOrderBy('s.position', 'ASC');

        $this->applyPublished($qb);

        return $qb->getQuery()->getResult();
    }

    public function findTopForMenu(int $limit): array
    {
        $qb = $this->createQueryBuilder('s')
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->setMaxResults($limit);

        $this->applyStorefrontCodes($qb);
        $this->applyPublished($qb);

        return $qb->getQuery()->getResult();
    }

    public function searchByName(string $name, int $limit): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('LOWER(s.name) LIKE LOWER(:name)')
            ->andWhere('s.img_link IS NOT NULL')
            ->andWhere('s.img_link != :empty')
            ->setParameter('empty', '')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('s.position', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->setMaxResults($limit);

        $this->applyStorefrontCodes($qb);
        $this->applyPublished($qb);

        return $qb->getQuery()->getResult();
    }
}
