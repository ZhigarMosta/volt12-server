<?php

namespace App\Controller\Volt12;

use App\Entity\CatalogItem;
use App\Provider\ProductCodeProvider;
use App\Repository\CatalogCharacteristicRepository;
use App\Repository\CatalogItemRepository;
use App\Service\Volt12\CatalogItemService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12')]
class CatalogItemController extends AbstractController
{
    public function __construct(
        private CatalogItemService $catalogItemService,
        private CatalogItemRepository $catalogItemRepository,
        private CatalogCharacteristicRepository $catalogCharacteristicRepository,
        private LoggerInterface $logger
    )
    {
    }

    private function productCodes(): array
    {
        return [ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY];
    }

    #[Route('/catalog_items/detail', name: 'volt12_catalog_item_detail', methods: ['POST'])]
    public function detail(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $slug = $data['slug'] ?? null;
        $recentlyViewedIds = $data['recently_viewed_ids'] ?? [];

        if (!$slug) {
            return $this->json(['success' => false, 'error' => 'Slug не указан'], 400);
        }

        $item = $this->catalogItemRepository->findBySlug($slug, $this->productCodes());
        if (!$item) {
            return $this->json(['success' => false, 'error' => 'Товар не найден'], 404);
        }

        $characteristics = $this->buildCharacteristics($item);

        $images = [];
        foreach ($item->getCatalogItemImages() as $image) {
            $images[] = [
                'id' => $image->getId(),
                'img_link' => $image->getImgLink(),
                'alt' => $image->getAlt(),
                'title' => $image->getTitle(),
                'position' => $image->getPosition(),
            ];
        }

        $related = $this->catalogItemRepository->findRelatedByName(
            $item->getName(),
            $item->getId(),
            $this->productCodes(),
            4
        );

        $recentlyViewed = $this->catalogItemRepository->findByIds(
            $recentlyViewedIds,
            $this->productCodes()
        );

        return $this->json([
            'success' => true,
            'item' => [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'slug' => $item->getSlug(),
                'price' => $item->getPrice(),
                'description' => $item->getDescription(),
                'short_description' => $item->getShortDescription(),
                'count' => $item->getCount(),
                'product_code' => $item->getProductCode(),
                'is_new' => $item->getIsNew(),
                'is_popular' => $item->getIsPopular(),
                'is_published' => $item->getIsPublished(),
                'position' => $item->getPosition(),
                'catalog_id' => $item->getCatalog()?->getId(),
                'images' => $images,
                'characteristics' => $characteristics,
            ],
            'related' => array_map(fn(CatalogItem $s) => [
                'id' => $s->getId(),
                'name' => $s->getName(),
                'slug' => $s->getSlug(),
                'price' => $s->getPrice(),
                'img_link' => $this->getFirstImageLink($s),
            ], $related),
            'recently_viewed' => array_map(fn(CatalogItem $s) => [
                'id' => $s->getId(),
                'name' => $s->getName(),
                'slug' => $s->getSlug(),
                'price' => $s->getPrice(),
                'img_link' => $this->getFirstImageLink($s),
            ], $recentlyViewed),
        ]);
    }

    private function getFirstImageLink(CatalogItem $item): ?string
    {
        $images = $item->getCatalogItemImages();
        if ($images->isEmpty()) {
            return null;
        }

        return $images->first()->getImgLink();
    }

    private function buildCharacteristics(CatalogItem $item): array
    {
        $catalog = $item->getCatalog();
        if (!$catalog) {
            return [];
        }

        $productCharacteristicIds = [];
        foreach ($item->getCharacteristics() as $itemCharacteristic) {
            $characteristic = $itemCharacteristic->getCatalogCharacteristic();
            if ($characteristic) {
                $productCharacteristicIds[$characteristic->getId()] = true;
            }
        }

        $characteristics = [];
        foreach ($this->catalogCharacteristicRepository->list($catalog->getId(), $this->productCodes()) as $catalogCharacteristic) {
            $characteristics[] = [
                'id' => $catalogCharacteristic['id'],
                'name' => $catalogCharacteristic['name'],
                'exist' => isset($productCharacteristicIds[$catalogCharacteristic['id']]),
            ];
        }

        return $characteristics;
    }
    /**
     *{
     * "filterGroups": [
     * [101, 102],  Красный ИЛИ Синий
     * [201],       Дерево
     * [900]        В наличии
     * ]
     * }
     */
    #[Route('/catalog_items', name: 'volt12_catalog_items', methods: ['POST'])]
    public function catalog_items(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $catalogId = $data['catalogId'] ?? null;

        $filterGroups = $data['filterGroups'] ?? [];
        $page = $data['page'] ?? 1;
        $limit = $data['limit'] ?? 10;
        $price = $data['price'] ?? [];
        $search = $data['search'] ?? '';
        $sortPrice = $data['sortPrice'] ?? null;

//        $this->logger->info('CatalogItemController: price', ['price' => $price]);

        $paginator = $this->catalogItemService->getCatalogItemByCatalogID($catalogId, $filterGroups, $price, $search,$sortPrice, $page, $limit);

        $facets = $this->catalogItemService->calculateFacets($catalogId, $filterGroups, $price, $search);

        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        $items = [];
        foreach ($paginator as $item) {
            $items[] = $item;
        }

        return $this->json([
            'items' => $items,
            'facets' => $facets,
            'meta' => [
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'limit' => $limit,
            ]
        ]);
    }

    #[Route('/popular_catalog_items', name: 'volt12_popular_catalog_items', methods: ['GET'])]
    public function popular_catalog_items(): JsonResponse
    {
        return $this->json($this->catalogItemService->getPopularCatalogItemList());
    }

    #[Route('/popular_catalog_items_by_first_popular_catalog', name: 'volt12_popular_catalog_items_by_first_popular_catalog', methods: ['GET'])]
    public function popular_catalog_items_by_catalog(): JsonResponse
    {
        return $this->json($this->catalogItemService->getCatalogItemListByFirstPopularCatalog());
    }
}
