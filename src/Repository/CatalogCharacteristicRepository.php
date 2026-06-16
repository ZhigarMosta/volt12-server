<?php

namespace App\Repository;

use App\Entity\CatalogCharacteristic;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class CatalogCharacteristicRepository extends EntityRepository
{
    /**
     * Получить список характеристик для фильтрации
     */
    public function list(int $catalogId, array $productCodes): array
    {
        return $this->createQueryBuilder('cc')
            ->select('cc.id', 'cc.name', 'IDENTITY(cc.catalogGroup) as group_id', 'cg.name as group_name')
            ->leftJoin('cc.catalogGroup', 'cg')
            ->where('cc.catalog = :catalogId')
            ->andWhere('cc.productCode IN (:productCodes)')
            ->setParameter('catalogId', $catalogId)
            ->setParameter('productCodes', $productCodes)
            ->orderBy('cc.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить все характеристики
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('cc')
            ->orderBy('cc.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить характеристики для сортировки по ID каталога
     */
    public function findForSortByCatalog(int $catalogId): array
    {
        return $this->createQueryBuilder('cc')
            ->where('cc.catalog = :catalogId')
            ->setParameter('catalogId', $catalogId)
            ->orderBy('cc.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить характеристики для сортировки по ID группы
     */
    public function findForSortByCatalogGroup(int $catalogGroupId): array
    {
        return $this->createQueryBuilder('cc')
            ->where('cc.catalogGroup = :catalogGroupId')
            ->setParameter('catalogGroupId', $catalogGroupId)
            ->orderBy('cc.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить характеристику по ID
     */
    public function findOneById(int $id): ?CatalogCharacteristic
    {
        return $this->find($id);
    }
}