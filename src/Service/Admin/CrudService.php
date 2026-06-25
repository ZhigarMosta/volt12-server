<?php

namespace App\Service\Admin;

use App\Entity\CatalogItem;
use App\Entity\CatalogItemImage;
use App\Entity\CatalogCharacteristic;
use App\Entity\CatalogGroup;
use App\Entity\FeedbackFromMap;
use App\Entity\ServiceGroup;

class CrudService
{
    public function transformForSelect($data): array
    {
        $select = [];
        foreach ($data as $item) {
            $select[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
            ];
        }
        return $select;
    }

    public function transformSortProduct($data): array
    {
        $select = [];
        foreach ($data as $item) {
            /* @var CatalogItem $item */
            $firstImage = $item->getCatalogItemImages()[0] ?? null;
            $select[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'img' => ['imgLink' => $firstImage?->getImgLink() ?? ''],
                'productCode' => $item->getProductCode(),
                'position' => $item->getPosition(),
            ];
        }
        return $select;
    }

    public function transformSortImage($data): array
    {
        $items = [];
        foreach ($data as $item) {
            /* @var CatalogItemImage $item */
            $items[] = [
                'id' => $item->getId(),
                'name' => $item->getTitle(),
                'img' => ['imgLink' => $item->getImgLink()],
                'position' => $item->getPosition(),
            ];
        }
        usort($items, fn($a, $b) => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));
        return $items;
    }

    public function transformSortFeedback($data): array
    {
        $items = [];
        foreach ($data as $item) {
            /* @var FeedbackFromMap $item */
            $items[] = [
                'id' => $item->getId(),
                'name' => $item->getUserName(),
                'img' => ['imgLink' => ''],
                'position' => $item->getPosition(),
            ];
        }
        usort($items, fn($a, $b) => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));
        return $items;
    }

    public function transformSortCatalogGroups($data): array
    {
        $items = [];
        foreach ($data as $item) {
            /* @var CatalogGroup $item */
            $items[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'img' => ['imgLink' => ''],
                'position' => $item->getPosition(),
            ];
        }
        usort($items, fn($a, $b) => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));
        return $items;
    }

    public function transformSortServices($data): array
    {
        $items = [];
        foreach ($data as $item) {
            /* @var \App\Entity\Service $item */
            $items[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'img' => ['imgLink' => $item->getImgLink() ?? ''],
                'position' => $item->getPosition(),
            ];
        }
        usort($items, fn($a, $b) => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));
        return $items;
    }

    public function transformSortCatalogCharacteristics($data): array
    {
        $items = [];
        foreach ($data as $item) {
            /* @var CatalogCharacteristic $item */
            $items[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'img' => ['imgLink' => ''],
                'position' => $item->getPosition(),
            ];
        }
        usort($items, fn($a, $b) => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));
        return $items;
    }

    public function transformSortServiceGroups($data): array
    {
        $items = [];
        foreach ($data as $item) {
            /* @var ServiceGroup $item */
            $items[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'img' => ['imgLink' => ''],
                'position' => $item->getPosition(),
            ];
        }
        usort($items, fn($a, $b) => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));
        return $items;
    }

    public function transformSortCharacteristic(array $data): array
    {
        $select = [];
        foreach ($data as $item) {
            if (!$item instanceof CatalogCharacteristic) {
                continue;
            }

            $select[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'position' => $item->getPosition(),
            ];
        }

        return $select;
    }
}
