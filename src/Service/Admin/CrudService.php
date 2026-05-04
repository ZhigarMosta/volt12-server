<?php

namespace App\Service\Admin;

use App\Entity\CatalogItem;

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
            $select[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'image' => $item->getCatalogItemImages()[0],
                'productCode' => $item->getProductCode(),
                'position' => $item->getPosition(),
            ];
        }
        return $select;
    }
}
