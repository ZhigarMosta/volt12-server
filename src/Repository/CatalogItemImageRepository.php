<?php

namespace App\Repository;

use App\Entity\CatalogItemImage;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CatalogItemImageRepository extends EntityRepository
{
    /**
     * Получить изображения для сортировки по ID товара
     */
    public function findForSortByCatalogItem(int $catalogItemId): array
    {
        return $this->createQueryBuilder('cii')
            ->where('cii.catalogItem = :catalogItemId')
            ->setParameter('catalogItemId', $catalogItemId)
            ->orderBy('cii.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}