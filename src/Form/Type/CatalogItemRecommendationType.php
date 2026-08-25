<?php

namespace App\Form\Type;

use App\Entity\CatalogItemRecommendation;

class CatalogItemRecommendationType extends AbstractCatalogItemLinkType
{
    protected function getDataClass(): string { return CatalogItemRecommendation::class; }
    protected function getModalKey(): string { return 'catalog_item_recommendations'; }
    protected function getAllByItemRoute(): string { return 'admin_crud_all_catalog_item_recommendations_by_catalog_item_id'; }
    protected function getSortRoute(): string { return 'admin_crud_sort_catalog_item_recommendations'; }
}
