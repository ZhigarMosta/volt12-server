<?php

namespace App\Controller\Admin;

use App\Entity\Catalog;
use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogGroup;
use App\Entity\CatalogItem;
use App\Entity\CatalogItemImage;
use App\Service\Admin\CrudService;
use App\Service\Admin\SortService;
use App\Service\Volt12\CatalogCharacteristicService;
use App\Service\Volt12\CatalogGroupService;
use App\Service\Volt12\CatalogItemService;
use App\Service\Volt12\CatalogItemImageService;
use App\Service\Volt12\CatalogService;
use App\Repository\CatalogItemImageRepository;
use App\Repository\CatalogCharacteristicRepository;
use App\Repository\CatalogGroupRepository;
use App\Repository\CatalogItemRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


#[Route('/admin/crud')]
class MainController extends AbstractController
{
    public function __construct(
        private CatalogItemService $catalogItemService,
        private CatalogCharacteristicService $catalogCharacteristicService,
        private CrudService $crudService,
        private SortService $sortService,
        private CatalogGroupService $catalogGroupService,
        private CatalogService $catalogService,
        private CatalogItemImageService $catalogItemImageService,
        private CatalogGroupRepository $catalogGroupRepository,
        private EntityManagerInterface $entityManager,  // ДОБАВИТЬ
        private ?LoggerInterface $logger = null
    ) {}

    // ========== СУЩЕСТВУЮЩИЕ РОУТЫ ==========
    
    #[Route('/catalog_characteristics_by_catalog_item/{id}', name: 'admin_crud_catalog_characteristics_by_catalog_item', methods: ['GET'])]
    public function catalogCharacteristicsByCatalogItem(CatalogItem $item): JsonResponse
    {
        $catalog = $item->getCatalog();
        return $this->json([
            'items' => $this->crudService->transformForSelect($catalog->getCharacteristics()),
            'messageInfo' => '⚠ Характеристики каталога отфильтрованы по каталогу продукта. Каталог: ' . $catalog->getName(),
        ]);
    }

    #[Route('/catalogs_by_group/{id}', name: 'admin_crud_catalogs_by_group', methods: ['GET'])]
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
            'items' => $this->crudService->transformForSelect($this->catalogItemService->getCatalogItemList())
        ]);
    }

    #[Route('/all_catalog_characteristic', name: 'admin_crud_all_catalog_characteristic', methods: ['GET'])]
    public function allCatalogCharacteristic(): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformForSelect($this->catalogCharacteristicService->getAll())
        ]);
    }

    #[Route('/all_catalog', name: 'admin_crud_all_catalog', methods: ['GET'])]
    public function allCatalog(): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformForSelect($this->catalogService->getAll())
        ]);
    }

    #[Route('/all_catalog_group', name: 'admin_crud_all_catalog_group', methods: ['GET'])]
    public function allCatalogGroups(): JsonResponse
    {
        return $this->json([
            'items' => $this->crudService->transformForSelect($this->catalogGroupService->getAll())
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

    #[Route('/groups_by_catalog/{id}', name: 'admin_crud_groups_by_catalog', methods: ['GET'])]
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

    // ========== СОРТИРОВКА ==========

    #[Route('/sort-catalog-items', name: 'admin_crud_sort_catalog_items', methods: ['POST'])]
    public function sortCatalogItems(Request $request): JsonResponse
    {
        return $this->json(
            $this->sortService->sort(
                $request->toArray(),
                CatalogItem::class,
                'position'
            )
        );
    }

    #[Route('/sort-catalog-item-images', name: 'admin_crud_sort_catalog_item_images', methods: ['POST'])]
    public function sortCatalogItemImages(Request $request): JsonResponse
    {
        return $this->json(
            $this->sortService->sort(
                $request->toArray(),
                CatalogItemImage::class,
                'position'
            )
        );
    }

    #[Route('/sort-catalog-characteristics', name: 'admin_crud_sort_catalog_characteristics', methods: ['POST'])]
    public function sortCatalogCharacteristics(Request $request): JsonResponse
    {
        return $this->json(
            $this->sortService->sort(
                $request->toArray(),
                CatalogCharacteristic::class,
                'position'
            )
        );
    }

    #[Route('/sort-catalog-groups', name: 'admin_crud_sort_catalog_groups', methods: ['POST'])]
    public function sortCatalogGroups(Request $request): JsonResponse
    {
        return $this->json(
            $this->sortService->sort(
                $request->toArray(),
                CatalogGroup::class,
                'position'
            )
        );
    }

    // ========== ПОЛУЧЕНИЕ ДАННЫХ ДЛЯ СОРТИРОВКИ ==========

    #[Route('/catalog-items-by-catalog/{id}/for-sort', name: 'admin_crud_all_catalog_items_by_catalog_id', methods: ['GET'])]
    public function allProductsByCatalog(Catalog $item): JsonResponse
    {
        $items = $this->sortService->getEntitiesForSort(
            CatalogItem::class,
            'catalog',
            $item->getId(),
            'position',
            'ASC',
            function (CatalogItem $catalogItem) {
                $firstImage = $catalogItem->getCatalogItemImages()->first();
                
                return [
                    'id' => $catalogItem->getId(),
                    'name' => $catalogItem->getName(),
                    'imgLink' => $firstImage ? $firstImage->getImgLink() : null,
                    'position' => $catalogItem->getPosition(),
                ];
            }
        );

        return $this->json(['items' => $items]);
    }

    #[Route('/catalog-item-images-by-item/{id}/for-sort', name: 'admin_crud_all_catalog_item_images_by_catalog_item_id', methods: ['GET'])]
    public function getCatalogItemImagesByCatalogItemId(int $id): JsonResponse
    {
        $items = $this->sortService->getEntitiesForSort(
            CatalogItemImage::class,
            'catalogItem',
            $id,
            'position',
            'ASC',
            function (CatalogItemImage $image) {
                return [
                    'id' => $image->getId(),
                    'name' => $image->getTitle() ?? $image->getAlt() ?? 'Изображение',
                    'imgLink' => $image->getImgLink(),
                    'position' => $image->getPosition(),
                ];
            }
        );

        return $this->json(['items' => $items]);
    }

    #[Route('/catalog-characteristics-by-catalog/{id}/for-sort', name: 'admin_crud_all_catalog_characteristics_by_catalog_id', methods: ['GET'])]
    public function getCatalogCharacteristicsByCatalogId(int $id): JsonResponse
    {
        // Получаем только характеристики БЕЗ группы
        $repository = $this->entityManager->getRepository(CatalogCharacteristic::class);
        $characteristics = $repository->findBy(
            [
                'catalog' => $id,
                'catalogGroup' => null  // ТОЛЬКО те, у которых нет группы
            ],
            ['position' => 'ASC']
        );

        $items = array_map(function (CatalogCharacteristic $characteristic) {
            return [
                'id' => $characteristic->getId(),
                'name' => $characteristic->getName(),
                'position' => $characteristic->getPosition(),
            ];
        }, $characteristics);

        return $this->json(['items' => $items]);
    }

    #[Route('/catalog-groups-by-catalog/{id}/for-sort', name: 'admin_crud_all_catalog_groups_by_catalog_id', methods: ['GET'])]
    public function getCatalogGroupsByCatalogId(int $id): JsonResponse
    {
        $items = $this->sortService->getEntitiesForSort(
            CatalogGroup::class,
            'catalog',
            $id,
            'position',
            'ASC',
            function (CatalogGroup $group) {
                return [
                    'id' => $group->getId(),
                    'name' => $group->getName(),
                    'position' => $group->getPosition(),
                ];
            }
        );

        return $this->json(['items' => $items]);
    }

    #[Route('/catalog-groups/{id}/characteristics', name: 'admin_catalog_group_characteristics_index', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getCatalogGroupCharacteristicsByCatalogGroupId(int $id): JsonResponse
    {
        $catalogGroup = $this->catalogGroupRepository->find($id);
        if (!$catalogGroup) {
            throw $this->createNotFoundException('Catalog group not found');
        }

        $items = $this->sortService->getEntitiesForSort(
            CatalogCharacteristic::class,
            'catalogGroup',
            $id,
            'position',
            'ASC',
            function (CatalogCharacteristic $characteristic) {
                return [
                    'id' => $characteristic->getId(),
                    'name' => $characteristic->getName(),
                    'position' => $characteristic->getPosition(),
                ];
            }
        );

        return $this->json(['items' => $items]);
    }

    #[Route('/catalog-groups/{id}/characteristics/sort', name: 'admin_catalog_group_characteristics_sort', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function sortCatalogGroupCharacteristics(Request $request): JsonResponse
    {
        return $this->json(
            $this->sortService->sort(
                $request->toArray(),
                CatalogCharacteristic::class,
                'position'
            )
        );
    }
}