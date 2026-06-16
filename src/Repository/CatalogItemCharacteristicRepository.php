<?php

namespace App\Repository;

use App\Entity\CatalogItemCharacteristic;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CatalogItemCharacteristicRepository extends EntityRepository
{
    /**
     * Получить характеристики товара для сортировки
     */
    public function findForSortByCatalogItem(int $catalogItemId): array
    {
        return $this->createQueryBuilder('cic')
            ->where('cic.catalogItem = :catalogItemId')
            ->setParameter('catalogItemId', $catalogItemId)
            ->orderBy('cic.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить запись по ID
     */
    public function findOneById(int $id): ?CatalogItemCharacteristic
    {
        return $this->find($id);
    }
}