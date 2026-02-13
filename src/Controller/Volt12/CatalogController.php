<?php

namespace App\Controller\Volt12;

use App\Service\Volt12\CatalogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/volt12')]
class CatalogController extends AbstractController
{
    public function __construct(
        private CatalogService $catalogService
    ) {}

    #[Route('/catalogs', name: 'volt12_list', methods: ['GET'])]
    public function catalogs(): JsonResponse
    {
        $data = $this->catalogService->getAllCatalogsData();

        return $this->json($data);
    }
}
