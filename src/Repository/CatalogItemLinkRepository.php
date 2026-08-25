<?php

namespace App\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

/**
 * Общий репозиторий табличек «товар — товар».
 */
class CatalogItemLinkRepository extends EntityRepository
{
    /**
     * Связки товара для админской сортировки — все, по позиции.
     *
     * @return \App\Entity\CatalogItemLink[]
     */
    public function findByOwnerOrdered(int $ownerId): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.linkedItem', 'ci')->addSelect('ci')
            ->where('l.catalogItem = :owner')
            ->setParameter('owner', $ownerId)
            ->orderBy('l.position', 'ASC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Связки товара для витрины: только опубликованные связанные товары
     * с подходящим кодом продукта, по позиции связки.
     *
     * @return \App\Entity\CatalogItemLink[]
     */
    public function findForStorefront(int $ownerId, array $productCodes): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.linkedItem', 'ci')->addSelect('ci')
            ->leftJoin('ci.catalogItemImages', 'img')->addSelect('img')
            ->where('l.catalogItem = :owner')
            ->andWhere('ci.is_published = true')
            ->andWhere('ci.product_code IN (:codes)')
            ->setParameter('owner', $ownerId)
            ->setParameter('codes', $productCodes)
            ->orderBy('l.position', 'ASC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
