<?php

namespace App\Controller\Admin;

use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogItem;
use App\Service\Admin\CrudService;
use App\Service\Volt12\CatalogCharacteristicService;
use App\Service\Volt12\CatalogItemService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/crud')]
class CatalogItemCharacteristicController extends AbstractController
{
    public function __construct(
        private CatalogItemService $catalogItemService,
        private CatalogCharacteristicService $catalogCharacteristicService,
        private CrudService $crudService,
    ) {}
    #[Route('/catalog_characteristics_by_catalog/{id}', name: 'admin_crud_catalog_characteristics_by_catalog', methods: ['GET'])]
    public function catalogCharacteristicsByCatalog(CatalogItem $item): JsonResponse
    {
        $catalog = $item->getCatalog();
        return $this->json([
            'items' => $this->crudService->transformForSelect($catalog->getCharacteristics()),
            'messageInfo' => '⚠ Характеристики каталога отфильтрованы по каталогу продукта. Каталог: ' . $catalog->getName(),
        ]);
    }

    #[Route('/all_products', name: 'admin_crud_all_products', methods: ['GET'])]
    public function allProducts(): JsonResponse
    {
        return $this->json([
            'items' =>$this->crudService->transformForSelect($this->catalogItemService->getCatalogItemList())
        ]);
    }

    #[Route('/all_catalog_characteristic', name: 'admin_crud_all_catalog_characteristic', methods: ['GET'])]
    public function allCatalogCharacteristic(): JsonResponse
    {
        return $this->json([
            'items' =>$this->crudService->transformForSelect($this->catalogCharacteristicService->getAll())
        ]);
    }

    #[Route('/products_by_characteristic/{id}', name: 'admin_crud_products_by_characteristic', methods: ['GET'])]
    public function productsByCharacteristic(CatalogCharacteristic $characteristic): JsonResponse
    {
        $catalog = $characteristic->getCatalog();
        return $this->json([
            'items' => $this->crudService->transformForSelect($catalog->getCatalogItems()),
            'messageInfo' => '⚠ Продукты отфильтрованы по каталогу характеристики каталога. Каталог: ' . $catalog->getName()
        ]);
    }

    #[Route('/check_catalog_match', name: 'admin_crud_check_catalog_match', methods: ['POST'])]
    public function checkCatalogMatch(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $catalogFromCatalogItem = $this->catalogItemService->getCatalogItemById((int)$data['catalogItemId'])->getCatalog();
        $catalogFromCatalogCharacteristic = $this->catalogCharacteristicService->getCatalogCharacteristicById((int)$data['catalogCharacteristicId'])->getCatalog();
        if (!$catalogFromCatalogItem || !$catalogFromCatalogCharacteristic) return $this->json(false);
        return $this->json($catalogFromCatalogItem->getId() === $catalogFromCatalogCharacteristic->getId());
    }
}
