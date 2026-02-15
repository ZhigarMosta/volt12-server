<?php

namespace App\Service\Admin;

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
}
