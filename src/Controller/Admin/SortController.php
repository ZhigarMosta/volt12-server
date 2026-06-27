<?php

namespace App\Controller\Admin;

use App\Entity\Catalog;
use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogGroup;
use App\Entity\CatalogItem;
use App\Entity\CatalogItemImage;
use App\Entity\FeedbackFromMap;
use App\Entity\Service;
use App\Entity\ServiceGroup;
use App\Repository\CatalogCharacteristicRepository;
use App\Repository\FeedbackFromMapRepository;
use App\Repository\ServiceGroupRepository;
use App\Service\Admin\CrudService;
use App\Service\Admin\SortService;
use App\Service\Volt12\CatalogCharacteristicService;
use App\Service\Volt12\CatalogGroupService;
use App\Service\Volt12\CatalogItemService;
use App\Service\Volt12\CatalogService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/crud')]
class SortController extends AbstractController
{
    public function __construct(
        private CatalogItemService $catalogItemService,
        private CatalogCharacteristicService $catalogCharacteristicService,
        private CrudService $crudService,
        private CatalogGroupService $catalogGroupService,
        private CatalogService $catalogService,
        private SortService $sortService,
        private FeedbackFromMapRepository $feedbackFromMapRepository,
        private ServiceGroupRepository $serviceGroupRepository,
        private CatalogCharacteristicRepository $catalogCharacteristicRepository,
        private ?LoggerInterface       $logger = null
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

    #[Route('/all_catalog_items_by_catalog_id/{id}', name: 'admin_crud_all_catalog_items_by_catalog_id', methods: ['GET'])]
    public function allProductsByCatalog(Catalog $item): JsonResponse
    {
        $result = $this->catalogItemService->getCatalogItemByCatalogID(
            catalogId: $item->getId(),
            page: null,
            limit: null
        );

        return $this->json([
            'items' => $this->crudService->transformSortProduct($result['items']),
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

    #[Route('/sort_catalog_items', name: 'admin_crud_sort_catalog_items', methods: ['POST'])]
    public function sortCatalogItems(Request $request): JsonResponse
    {
        return $this->json($this->sortService->sort(CatalogItem::class, $request->toArray()));
    }

    #[Route('/all_catalog_item_images_by_catalog_item_id/{id}', name: 'admin_crud_all_catalog_item_images_by_catalog_item_id', methods: ['GET'])]
    public function allImagesByCatalogItem(CatalogItem $item): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformSortImage($item->getCatalogItemImages()),
        ]);
    }

    #[Route('/sort_catalog_item_images', name: 'admin_crud_sort_catalog_item_images', methods: ['POST'])]
    public function sortCatalogItemImages(Request $request): JsonResponse
    {
        return $this->json($this->sortService->sort(CatalogItemImage::class, $request->toArray()));
    }

    #[Route('/all_feedback_from_map', name: 'admin_crud_all_feedback_from_map', methods: ['GET'])]
    public function allFeedbackFromMap(): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformSortFeedback(
                $this->feedbackFromMapRepository->findBy([], ['position' => 'ASC'])
            ),
        ]);
    }

    #[Route('/sort_feedback_from_map', name: 'admin_crud_sort_feedback_from_map', methods: ['POST'])]
    public function sortFeedbackFromMap(Request $request): JsonResponse
    {
        return $this->json($this->sortService->sort(FeedbackFromMap::class, $request->toArray()));
    }

    #[Route('/all_catalog_groups_by_catalog_id/{id}', name: 'admin_crud_all_catalog_groups_by_catalog_id', methods: ['GET'])]
    public function allCatalogGroupsByCatalog(Catalog $item): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformSortCatalogGroups($item->getGroups()),
        ]);
    }

    #[Route('/sort_catalog_groups', name: 'admin_crud_sort_catalog_groups', methods: ['POST'])]
    public function sortCatalogGroups(Request $request): JsonResponse
    {
        return $this->json($this->sortService->sort(CatalogGroup::class, $request->toArray()));
    }

    #[Route('/all_catalog_characteristics_by_group_id/{id}', name: 'admin_crud_all_catalog_characteristics_by_group_id', methods: ['GET'])]
    public function allCatalogCharacteristicsByGroup(CatalogGroup $item): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformSortCatalogCharacteristics(
                $this->catalogCharacteristicRepository->findByGroup($item->getId())
            ),
        ]);
    }

    #[Route('/all_catalog_characteristics_without_group_by_catalog_id/{id}', name: 'admin_crud_all_catalog_characteristics_without_group_by_catalog_id', methods: ['GET'])]
    public function allCatalogCharacteristicsWithoutGroup(Catalog $item): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformSortCatalogCharacteristics(
                $this->catalogCharacteristicRepository->findByCatalogWithoutGroup($item->getId())
            ),
        ]);
    }

    #[Route('/sort_catalog_characteristics', name: 'admin_crud_sort_catalog_characteristics', methods: ['POST'])]
    public function sortCatalogCharacteristics(Request $request): JsonResponse
    {
        return $this->json($this->sortService->sort(CatalogCharacteristic::class, $request->toArray()));
    }

    #[Route('/all_services_by_service_group_id/{id}', name: 'admin_crud_all_services_by_service_group_id', methods: ['GET'])]
    public function allServicesByServiceGroup(ServiceGroup $item): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformSortServices(
                $item->getServices()->toArray()
            ),
        ]);
    }

    #[Route('/sort_services', name: 'admin_crud_sort_services', methods: ['POST'])]
    public function sortServices(Request $request): JsonResponse
    {
        return $this->json($this->sortService->sort(Service::class, $request->toArray()));
    }

    #[Route('/all_service_groups', name: 'admin_crud_all_service_groups', methods: ['GET'])]
    public function allServiceGroups(): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformSortServiceGroups(
                $this->serviceGroupRepository->findBy([], ['position' => 'ASC'])
            ),
        ]);
    }

    #[Route('/sort_service_groups', name: 'admin_crud_sort_service_groups', methods: ['POST'])]
    public function sortServiceGroups(Request $request): JsonResponse
    {
        return $this->json($this->sortService->sort(ServiceGroup::class, $request->toArray()));
    }

}
