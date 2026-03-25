<?php

namespace App\Service\Volt12;

use App\Entity\CatalogItem;
use App\Provider\ProductCodeProvider;
use App\Repository\CatalogCharacteristicRepository;
use App\Repository\CatalogItemRepository;
use App\Repository\CatalogRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CatalogItemService
{
    public function __construct(
        private CatalogItemRepository $catalogItemRepository,
        private CatalogRepository $catalogRepository,
        private CatalogCharacteristicRepository $catalogCharacteristicRepository
    )
    {
    }
    private array $productCodes = [ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY];
    public function getCatalogItemByCatalogID(int $catalogId, array $filterGroups, int $page = 1, int $limit = 10): Paginator
    {
        return $this->catalogItemRepository->list($this->productCodes, $catalogId, $filterGroups, $page, $limit);
    }

    /**
     * Основной метод для расчета фасетов (Умный подсчет N+1)
     */
    public function calculateFacets(int $catalogId, array $filterGroups): array
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

}
