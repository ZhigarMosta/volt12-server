<?php

namespace App\Service\Volt12;

use App\Entity\CatalogItem;
use App\Provider\ProductCodeProvider;
use App\Repository\CatalogCharacteristicRepository;
use App\Repository\CatalogItemRepository;
use App\Repository\CatalogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Log\LoggerInterface;

class CatalogItemService
{
    public function __construct(
        private CatalogItemRepository $catalogItemRepository,
        private CatalogRepository $catalogRepository,
        private CatalogCharacteristicRepository $catalogCharacteristicRepository,
        private EntityManagerInterface $entityManager,
        private ?LoggerInterface $logger = null,
    )
    {
    }
    private array $productCodes = [ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY];

    public function getCatalogItemByCatalogID(int $catalogId, ?array $filterGroups = [], ?array $price = [], ?string $search = null, ?int $sortPrice = null, ?int $page = 1, ?int $limit = 10)
    {
        if($sortPrice === 1){
            $sortPrice = 'ASC';
        } elseif ($sortPrice === 2){
            $sortPrice = 'DESC';
        }

        return $this->catalogItemRepository->list($this->productCodes, $catalogId, $filterGroups, $price, $search, $sortPrice, $page, $limit);
    }

    /**
     * Основной метод для расчета фасетов (Умный подсчет N+1)
     */
    public function calculateFacets(int $catalogId, array $filterGroups, array $price, string $search): array
    {
        $allChars = $this->catalogCharacteristicRepository->list($catalogId, $this->productCodes);

        $charIdToGroupName = [];
        $groupNameToCharIds = [];

        foreach ($allChars as $char) {
            $groupName = $char['group_name'] ?? 'standalone_common';
            $charId = $char['id'];

            $charIdToGroupName[$charId] = $groupName;
            $groupNameToCharIds[$groupName][] = $charId;
        }

        $activeGroupsMap = [];

        foreach ($filterGroups as $index => $ids) {
            if (empty($ids)) continue;
            $firstId = $ids[0];
            if (isset($charIdToGroupName[$firstId])) {
                $groupName = $charIdToGroupName[$firstId];
                $activeGroupsMap[$index] = $groupName;
            }
        }

        $finalFacets = [];

        foreach ($activeGroupsMap as $filterIndex => $groupName) {
            $targetIds = $groupNameToCharIds[$groupName] ?? [];

            if (empty($targetIds)) continue;

            $counts = $this->catalogItemRepository->getFacetCounts(
                $catalogId,
                $this->productCodes,
                $filterGroups,
                $price,
                $search,
                $filterIndex,
                $targetIds
            );

            foreach ($counts as $row) {
                $finalFacets[$row['char_id']] = (int)$row['item_count'];
            }
        }

        $activeGroupNames = array_values($activeGroupsMap);
        $allGroupNames = array_keys($groupNameToCharIds);
        $passiveGroupNames = array_diff($allGroupNames, $activeGroupNames);

        $passiveCharIds = [];
        foreach ($passiveGroupNames as $gName) {
            if (isset($groupNameToCharIds[$gName])) {
                foreach ($groupNameToCharIds[$gName] as $id) {
                    $passiveCharIds[] = $id;
                }
            }
        }

        if (!empty($passiveCharIds)) {
            $passiveCounts = $this->catalogItemRepository->getFacetCounts(
                $catalogId,
                $this->productCodes,
                $filterGroups,
                $price,
                $search,
                null,
                $passiveCharIds
            );

            foreach ($passiveCounts as $row) {
                $finalFacets[$row['char_id']] = (int)$row['item_count'];
            }
        }

        return $finalFacets;
    }
    public function getCatalogItemList(): array
    {
        return $this->catalogItemRepository->findAll();
    }

    public function getCatalogItemById(int $id): ?CatalogItem //TODO IDE не понимает какой конкретно объект возвращается
    {
        return $this->catalogItemRepository->find($id);
    }

    public function getPopularCatalogItemList()
    {
        return $this->catalogItemRepository->findPopular([ProductCodeProvider::CODE_ANY,ProductCodeProvider::CODE_VOLT12]);
    }

    public function getCatalogItemListByFirstPopularCatalog() {
        $code = [ProductCodeProvider::CODE_ANY,ProductCodeProvider::CODE_VOLT12];
        return $this->catalogItemRepository->findPopularByFirstPopularCatalog($code,$this->catalogRepository->firstPopular($code));
    }

    public function sortCatalogItem(?array $data): null|int
    {
        $items = $data['items'];
        $current = $data['current'];

        if (empty($items)) {
            return null;
        }

        $ids = array_column($items, 'id');
        $products = $this->catalogItemRepository->findBy(['id' => $ids]);

        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product->getId()] = $product;
        }

        foreach ($items as $itemData) {
            if (!isset($itemData['id']) || !isset($itemData['position'])) {
                continue;
            }

            if (!isset($productMap[$itemData['id']])) {
                continue;
            }

            $productMap[$itemData['id']]->setPosition($itemData['position']);
            $this->entityManager->persist($productMap[$itemData['id']]);
        }

        $this->entityManager->flush();

        if($current===-1){
            return $items[array_search($current, array_column($items, 'id'))]['position'] ?? null;
        }
        else{
            if(!$current){
               return null;
            }
            return $productMap[$current]->getPosition()??null;
        }
    }
}
