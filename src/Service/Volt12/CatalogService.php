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
        $rows = $this->catalogRepository->list([ProductCodeProvider::CODE_VOLT12,ProductCodeProvider::CODE_ANY]);

        return array_map(static function (array $row): array {
            $row['seo'] = [
                'meta_title' => $row['seo_meta_title'] ?? null,
                'meta_description' => $row['seo_meta_description'] ?? null,
                'meta_keywords' => $row['seo_meta_keywords'] ?? null,
                'noindex' => (bool) ($row['seo_noindex'] ?? false),
                'nofollow' => (bool) ($row['seo_nofollow'] ?? false),
                'canonical_url' => $row['seo_canonical_url'] ?? null,
            ];
            unset(
                $row['seo_meta_title'],
                $row['seo_meta_description'],
                $row['seo_meta_keywords'],
                $row['seo_noindex'],
                $row['seo_nofollow'],
                $row['seo_canonical_url'],
            );

            return $row;
        }, $rows);
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
