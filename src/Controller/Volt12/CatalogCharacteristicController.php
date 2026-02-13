<?php

namespace App\Controller\Volt12;

use App\Service\Volt12\CatalogCharacteristicService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12')]
class CatalogCharacteristicController extends AbstractController
{
    public function __construct(
        private CatalogCharacteristicService $catalogCharacteristicService
    ) {}

    #[Route('/catalog_characteristics', name: 'volt12_catalog_characteristics', methods: ['POST'])]
    public function catalog_items(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $catalogId = $data['catalogId'];
        return $this->json($this->catalogCharacteristicService->getCatalogCharacteristics($catalogId));
    }
}
