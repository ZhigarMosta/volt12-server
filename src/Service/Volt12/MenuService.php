<?php

namespace App\Service\Volt12;

use App\Entity\Catalog;
use App\Entity\CatalogItem;
use App\Entity\Service;
use App\Provider\ProductCodeProvider;
use App\Repository\CatalogItemRepository;
use App\Repository\CatalogRepository;
use App\Repository\ServiceRepository;

class MenuService
{
    private const CATALOGS_LIMIT = 6;
    private const ITEMS_PER_CATALOG = 5;
    private const SERVICES_LIMIT = 6;
    private const SEARCH_LIMIT = 5;

    public function __construct(
        private CatalogRepository $catalogRepository,
        private CatalogItemRepository $catalogItemRepository,
        private ServiceRepository $serviceRepository,
    ) {}

    public function search(string $name): array
    {
        $productCodes = [ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY];

        $catalogItems = $this->catalogItemRepository->searchByName($name, $productCodes, self::SEARCH_LIMIT);
        $services = $this->serviceRepository->searchByName($name, self::SEARCH_LIMIT);

        $result = [];

        foreach ($catalogItems as $catalogItem) {
            /** @var CatalogItem $catalogItem */
            $firstImage = $catalogItem->getCatalogItemImages()->first();
            $result[] = [
                'id'       => $catalogItem->getId(),
                'type'     => 'product',
                'name'     => $catalogItem->getName(),
                'slug'     => $catalogItem->getSlug(),
                'img_link' => $firstImage,
            ];
        }

        foreach ($services as $service) {
            /** @var Service $service */
            $result[] = [
                'id'       => $service->getId(),
                'type'     => 'service',
                'name'     => $service->getName(),
                'slug'     => $service->getSlug(),
                'img_link' => $service->getImgLink(),
            ];
        }

        return $result;
    }

    public function getCatalogMenu(): array
    {
        $productCodes = [ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY];

        $catalogs = $this->catalogRepository->findTopForMenu($productCodes, self::CATALOGS_LIMIT);
        $catalogIds = array_map(static fn(Catalog $catalog) => $catalog->getId(), $catalogs);

        $itemsByCatalogId = $this->groupMenuItems(
            $this->catalogItemRepository->findForMenuByCatalogIds($catalogIds, $productCodes),
            self::ITEMS_PER_CATALOG
        );

        $services = $this->serviceRepository->findTopForMenu(self::SERVICES_LIMIT);

        return [
            'catalogs' => array_map(
                fn(Catalog $catalog) => [
                    'id' => $catalog->getId(),
                    'name' => $catalog->getName(),
                    'slug' => $catalog->getSlug(),
                    'position' => $catalog->getPosition(),
                    'items' => array_map(
                        static fn(CatalogItem $item) => [
                            'id' => $item->getId(),
                            'name' => $item->getName(),
                            'slug' => $item->getSlug(),
                            'position' => $item->getPosition(),
                        ],
                        $itemsByCatalogId[$catalog->getId()] ?? []
                    ),
                ],
                $catalogs
            ),
            'services' => array_map(
                static fn(Service $service) => [
                    'id' => $service->getId(),
                    'name' => $service->getName(),
                    'slug' => $service->getSlug(),
                    'position' => $service->getPosition(),
                ],
                $services
            ),
        ];
    }

    /**
     * @param CatalogItem[] $items
     * @return array<int, CatalogItem[]>
     */
    private function groupMenuItems(array $items, int $limitPerCatalog): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $catalogId = $item->getCatalog()?->getId();
            if ($catalogId === null) {
                continue;
            }

            if (!isset($grouped[$catalogId])) {
                $grouped[$catalogId] = [];
            }

            if (count($grouped[$catalogId]) >= $limitPerCatalog) {
                continue;
            }

            $grouped[$catalogId][] = $item;
        }

        return $grouped;
    }
}
