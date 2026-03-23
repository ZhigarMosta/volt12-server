<?php

namespace App\Service\Volt12;

use App\Entity\Catalog;
use App\Provider\ProductCodeProvider;
use App\Repository\CatalogRepository;

class CatalogService
{
    public function __construct(
        private CatalogRepository $catalogRepository
    )
    {
    }

    public function getAllCatalogsData(): array
    {
        return $this->catalogRepository->list([ProductCodeProvider::CODE_VOLT12,ProductCodeProvider::CODE_ANY]);
    }

    public function getPopularCatalogs(): array
    {
        return $this->catalogRepository->popularListWithLimit([ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY]);
    }

    public function getCatalogsById(string $id): array
    {
        return $this->catalogRepository->byId($id);
    }

    public function getAll()
    {
        return $this->catalogRepository->findAll();
    }

    public function getCatalogById(int $id): ?Catalog
    {
        return $this->catalogRepository->find($id);
    }
}
