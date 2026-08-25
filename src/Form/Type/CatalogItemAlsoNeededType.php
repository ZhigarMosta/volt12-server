<?php

namespace App\Form\Type;

use App\Entity\CatalogItemAlsoNeeded;

class CatalogItemAlsoNeededType extends AbstractCatalogItemLinkType
{
    protected function getDataClass(): string { return CatalogItemAlsoNeeded::class; }
    protected function getModalKey(): string { return 'catalog_item_also_needed'; }
    protected function getAllByItemRoute(): string { return 'admin_crud_all_catalog_item_also_needed_by_catalog_item_id'; }
    protected function getSortRoute(): string { return 'admin_crud_sort_catalog_item_also_needed'; }
}
