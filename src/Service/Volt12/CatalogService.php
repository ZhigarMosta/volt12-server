<?php

namespace App\Service\Volt12;

use App\Provider\ProductCodeProvider;
use App\Repository\CatalogRepository;

class CatalogService
{
    public function __construct(
        private CatalogRepository $catalogRepository
    ) {}

    public function getAllCatalogsData(): array
    {
        return $this->catalogRepository->list(ProductCodeProvider::CODE_VOLT12);
    }
}
