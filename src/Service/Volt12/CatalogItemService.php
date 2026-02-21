<?php

namespace App\Service\Volt12;

use App\Entity\CatalogItem;
use App\Provider\ProductCodeProvider;
use App\Repository\CatalogItemRepository;

class CatalogItemService
{
    public function __construct(
        private CatalogItemRepository $catalogItemRepository
    )
    {
    }

    public function getCatalogItemByCatalogID(int $catalogId, array $characteristicIds): array
    {
        return $this->catalogItemRepository->list([ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY], $catalogId, $characteristicIds);
    }

    public function getCatalogItemList(): array
    {
        return $this->catalogItemRepository->findAll();
    }

    public function getCatalogItemById(int $id): ?CatalogItem //TODO IDE не понимает какой конкретно объект возвращается
    {
        return $this->catalogItemRepository->find($id);
    }
}
