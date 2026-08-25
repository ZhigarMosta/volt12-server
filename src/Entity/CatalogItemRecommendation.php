<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Блок «Рекомендуемые товары» на странице товара. */
#[ORM\Entity]
#[ORM\Table(name: 'catalog_item_recommendations')]
#[ORM\UniqueConstraint(name: 'uniq_ci_recommendations_pair', columns: ['catalog_item_id', 'linked_item_id'])]
#[ORM\Index(columns: ['catalog_item_id'], name: 'idx_ci_recommendations_item')]
class CatalogItemRecommendation extends CatalogItemLink
{
    public static function getBlockLabel(): string
    {
        return 'Рекомендуемые товары';
    }
}
