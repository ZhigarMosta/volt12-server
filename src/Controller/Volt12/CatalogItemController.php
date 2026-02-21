<?php

namespace App\Controller\Volt12;

use App\Service\Volt12\CatalogItemService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12')]
class CatalogItemController extends AbstractController
{
    public function __construct(
        private CatalogItemService $catalogItemService
    ) {}

    #[Route('/catalog_items', name: 'volt12_catalog_items', methods: ['POST'])]
    public function catalog_items(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $catalogId = $data['catalogId'];
        $characteristicIds = $data['characteristicIds'];
        return $this->json($this->catalogItemService->getCatalogItemByCatalogID($catalogId, $characteristicIds));
    }
}
