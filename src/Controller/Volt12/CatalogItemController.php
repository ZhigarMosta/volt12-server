<?php

namespace App\Controller\Volt12;

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
        private LoggerInterface $logger
    )
    {
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
