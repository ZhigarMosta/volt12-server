<?php

namespace App\Service\Volt12;

use App\Entity\CatalogItem;
use App\Provider\ProductCodeProvider;
use App\Repository\CatalogItemRepository;
use App\Repository\CatalogRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CatalogItemService
{
    public function __construct(
        private CatalogItemRepository $catalogItemRepository,
        private CatalogRepository $catalogRepository,
    )
    {
    }

    public function getCatalogItemByCatalogID(int $catalogId, array $characteristicIds, int $page = 1, int $limit = 10): Paginator
    {
        return $this->catalogItemRepository->list([ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY], $catalogId, $characteristicIds, $page, $limit);
    }

    public function getCatalogItemList(): array
    {
        return $this->catalogItemRepository->findAll();
    }

    public function getCatalogItemById(int $id): ?CatalogItem //TODO IDE не понимает какой конкретно объект возвращается
    {
        return $this->catalogItemRepository->find($id);
    }

    public function getPopularCatalogItemList()
    {
        return $this->catalogItemRepository->findPopular([ProductCodeProvider::CODE_ANY,ProductCodeProvider::CODE_VOLT12]);
    }

    public function getCatalogItemListByFirstPopularCatalog() {
        $code = [ProductCodeProvider::CODE_ANY,ProductCodeProvider::CODE_VOLT12];
        return $this->catalogItemRepository->findPopularByFirstPopularCatalog($code,$this->catalogRepository->firstPopular($code));
    }

}
