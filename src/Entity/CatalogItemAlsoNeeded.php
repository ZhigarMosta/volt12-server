<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Блок «Вам также может понадобиться» на странице товара. */
#[ORM\Entity]
#[ORM\Table(name: 'catalog_item_also_needed')]
#[ORM\UniqueConstraint(name: 'uniq_ci_also_needed_pair', columns: ['catalog_item_id', 'linked_item_id'])]
#[ORM\Index(columns: ['catalog_item_id'], name: 'idx_ci_also_needed_item')]
class CatalogItemAlsoNeeded extends CatalogItemLink
{
    public static function getBlockLabel(): string
    {
        return 'Вам также может понадобиться';
    }
}
