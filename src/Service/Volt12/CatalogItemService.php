<?php

namespace App\Service\Volt12;

use App\Provider\ProductCodeProvider;
use App\Repository\CatalogItemRepository;

class CatalogItemService
{
    public function __construct(
        private CatalogItemRepository $catalogItemRepository
    ) {}

    public function getCatalogItemByCatalogID(int $catalogId): array
    {
        return $this->catalogItemRepository->list(ProductCodeProvider::CODE_VOLT12,$catalogId,[]);
    }
}
