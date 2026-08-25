<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Блок «С этим товаром покупают» на странице товара. */
#[ORM\Entity]
#[ORM\Table(name: 'catalog_item_bought_together')]
#[ORM\UniqueConstraint(name: 'uniq_ci_bought_together_pair', columns: ['catalog_item_id', 'linked_item_id'])]
#[ORM\Index(columns: ['catalog_item_id'], name: 'idx_ci_bought_together_item')]
class CatalogItemBoughtTogether extends CatalogItemLink
{
    public static function getBlockLabel(): string
    {
        return 'С этим товаром покупают';
    }
}
