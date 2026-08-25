<?php

namespace App\Form\Type;

use App\Entity\CatalogItemBoughtTogether;

class CatalogItemBoughtTogetherType extends AbstractCatalogItemLinkType
{
    protected function getDataClass(): string { return CatalogItemBoughtTogether::class; }
    protected function getModalKey(): string { return 'catalog_item_bought_together'; }
    protected function getAllByItemRoute(): string { return 'admin_crud_all_catalog_item_bought_together_by_catalog_item_id'; }
    protected function getSortRoute(): string { return 'admin_crud_sort_catalog_item_bought_together'; }
}
