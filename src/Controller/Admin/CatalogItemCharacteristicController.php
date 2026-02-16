<?php

namespace App\Controller\Admin;

use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogGroup;
use App\Entity\CatalogItem;
use App\Repository\CatalogGroupRepository;
use App\Service\Admin\CrudService;
use App\Service\Volt12\CatalogCharacteristicService;
use App\Service\Volt12\CatalogGroupService;
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
        private CatalogGroupService $catalogGroupService,
    ) {}
    #[Route('/catalog_characteristics_by_catalog_item/{id}', name: 'admin_crud_catalog_characteristics_by_catalog_item', methods: ['GET'])]
    public function catalogCharacteristicsByCatalogItem(CatalogItem $item): JsonResponse
    {
        $catalog = $item->getCatalog();
        return $this->json([
            'items' => $this->crudService->transformForSelect($catalog->getCharacteristics()),
            'messageInfo' => '⚠ Характеристики каталога отфильтрованы по каталогу продукта. Каталог: ' . $catalog->getName(),
        ]);
    }

    #[Route('/catalog_characteristics_by_group/{id}', name: 'admin_crud_catalog_characteristics_by_group', methods: ['GET'])]
    public function catalogCharacteristicsByGroup(CatalogGroup $item): JsonResponse
    {
        $catalog = $item->getCatalog();
        return $this->json([
            'items' => $this->crudService->transformForSelect($catalog->getCharacteristics()),
            'messageInfo' => '⚠ Характеристики каталога отфильтрованы по каталогу группы. Каталог: ' . $catalog->getName(),
        ]);
    }

    #[Route('/all_catalog_items', name: 'admin_crud_all_catalog_items', methods: ['GET'])]
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

    #[Route('/all_catalog_group', name: 'admin_crud_all_catalog_group', methods: ['GET'])]
    public function allCatalogGroups(): JsonResponse
    {
        return $this->json([
            'items' =>$this->crudService->transformForSelect($this->catalogGroupService->getAll())
        ]);
    }

    #[Route('/catalog_items_by_characteristic/{id}', name: 'admin_crud_catalog_items_by_characteristic', methods: ['GET'])]
    public function CatalogItemsByCharacteristic(CatalogCharacteristic $characteristic): JsonResponse
    {
        $catalog = $characteristic->getCatalog();
        return $this->json([
            'items' => $this->crudService->transformForSelect($catalog->getCatalogItems()),
            'messageInfo' => '⚠ Продукты отфильтрованы по каталогу характеристики каталога. Каталог: ' . $catalog->getName()
        ]);
    }

    #[Route('/groups_by_characteristic/{id}', name: 'admin_crud_groups_by_characteristic', methods: ['GET'])]
    public function productsByCharacteristic(CatalogCharacteristic $characteristic): JsonResponse
    {
        $catalog = $characteristic->getCatalog();
        return $this->json([
            'items' => $this->crudService->transformForSelect($catalog->getGroups()),
            'messageInfo' => '⚠ Группы отфильтрованы по каталогу характеристики каталога. Каталог: ' . $catalog->getName()
        ]);
    }

    #[Route('/check_catalog_match_between_catalog_item_and_catalog_characteristic', name: 'admin_crud_check_catalog_match_between_catalog_item_and_catalog_characteristic', methods: ['POST'])]
    public function checkCatalogMatch(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $catalogFromCatalogItem = $this->catalogItemService->getCatalogItemById((int)$data['catalogItemId'])->getCatalog();
        $catalogFromCatalogCharacteristic = $this->catalogCharacteristicService->getCatalogCharacteristicById((int)$data['catalogCharacteristicId'])->getCatalog();
        if (!$catalogFromCatalogItem || !$catalogFromCatalogCharacteristic) return $this->json(false);
        return $this->json($catalogFromCatalogItem->getId() === $catalogFromCatalogCharacteristic->getId());
    }

    #[Route('/check_catalog_match_between_catalog_group_and_catalog_characteristic', name: 'admin_crud_check_catalog_match_between_catalog_group_and_catalog_characteristic', methods: ['POST'])]
    public function checkCatalogMatch2(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $catalogFromCatalogGroup = $this->catalogGroupService->getCatalogGroupById((int)$data['catalogGroupId'])->getCatalog();
        $catalogFromCatalogCharacteristic = $this->catalogCharacteristicService->getCatalogCharacteristicById((int)$data['catalogCharacteristicId'])->getCatalog();
        if (!$catalogFromCatalogGroup || !$catalogFromCatalogCharacteristic) return $this->json(false);
        return $this->json($catalogFromCatalogGroup->getId() === $catalogFromCatalogCharacteristic->getId());
    }
}
