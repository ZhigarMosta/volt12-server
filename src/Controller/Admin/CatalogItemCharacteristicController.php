<?php

namespace App\Controller\Admin;

use App\Entity\Catalog;
use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogGroup;
use App\Entity\CatalogItem;
use App\Service\Admin\CrudService;
use App\Service\Volt12\CatalogCharacteristicService;
use App\Service\Volt12\CatalogGroupService;
use App\Service\Volt12\CatalogItemService;
use App\Service\Volt12\CatalogService;
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
        private CatalogService $catalogService,
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

    #[Route('/catalogs_by_group/{id}', name: 'admin_crud_catalogs_by_group', methods: ['GET'])] //2
    public function catalogCharacteristicsByGroup(CatalogGroup $item): JsonResponse
    {
        $catalog = $item->getCatalog();
        return $this->json([
            'items' => $this->crudService->transformForSelect($this->catalogService->getCatalogsById($item->getCatalog()->getId())),
            'messageInfo' => '⚠ Каталоги отфильтрованы по каталогу группы. Каталог: ' . $catalog->getName(),
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

    #[Route('/all_catalog', name: 'admin_crud_all_catalog', methods: ['GET'])]
    public function allCatalog(): JsonResponse
    {
        return $this->json([
            'items' =>$this->crudService->transformForSelect($this->catalogService->getAll())
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

    #[Route('/groups_by_catalog/{id}', name: 'admin_crud_groups_by_catalog', methods: ['GET'])] //1
    public function productsByCharacteristic(Catalog $catalog): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformForSelect($catalog->getGroups()),
            'messageInfo' => '⚠ Группы отфильтрованы по каталогу. Каталог: ' . $catalog->getName()
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

    #[Route('/check_catalog_match_between_catalog_group_and_catalog_characteristic', name: 'admin_crud_check_catalog_match_between_catalog_group_and_catalog', methods: ['POST'])]
    public function checkCatalogMatch2(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $catalogFromCatalogGroup = $this->catalogGroupService->getCatalogGroupById((int)$data['catalogGroupId'])->getCatalog();
        $catalogFromCatalogCharacteristic = $this->catalogService->getCatalogById((int)$data['catalogId']);
        if (!$catalogFromCatalogGroup || !$catalogFromCatalogCharacteristic) return $this->json(false);
        return $this->json($catalogFromCatalogGroup->getId() === $catalogFromCatalogCharacteristic->getId());
    }
}
