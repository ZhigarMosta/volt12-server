<?php

namespace App\Controller\Volt12;

use App\Service\Volt12\CatalogService;
use App\Service\Volt12\MenuService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12')]
class CatalogController extends AbstractController
{
    public function __construct(
        private CatalogService $catalogService,
        private MenuService $menuService,
    ) {}

    #[Route('/catalogs', name: 'volt12_list', methods: ['GET'])]
    public function catalogs(): JsonResponse
    {
        $data = $this->catalogService->getAllCatalogsData();

        return $this->json($data);
    }

    #[Route('/popular_catalogs', name: 'volt12_popular_list', methods: ['GET'])]
    public function popular_catalogs(): JsonResponse
    {
        $data = $this->catalogService->getPopularCatalogs();

        return $this->json($data);
    }

    #[Route('/catalog-menu', name: 'volt12_catalog_menu', methods: ['GET'])]
    public function catalogMenu(): JsonResponse
    {
        return $this->json([
            'success' => true,
            ...$this->menuService->getCatalogMenu(),
        ]);
    }

    #[Route('/search', name: 'volt12_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $name = trim((string)$request->query->get('name', ''));

        if ($name === '') {
            return $this->json(['success' => false, 'error' => 'Параметр name обязателен'], 400);
        }

        return $this->json([
            'success' => true,
            'items'   => $this->menuService->search($name),
        ]);
    }
}
