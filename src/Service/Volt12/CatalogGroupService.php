<?php

namespace App\Service\Volt12;

use App\Entity\CatalogGroup;
use App\Repository\CatalogGroupRepository;

class CatalogGroupService
{
    public function __construct(
        private CatalogGroupRepository $catalogGroupRepository,
    ) {}

    public function getAll()
    {
        return $this->catalogGroupRepository->findAll();
    }

    public function getCatalogGroupById(int $id): ?CatalogGroup
    {
        return $this->catalogGroupRepository->find($id);
    }
}
