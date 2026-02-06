<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class CatalogItemCharacteristicMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog
                ->addChild('catalog_item_characteristic', ['route' => 'app_admin_catalog_item_characteristic_index'])
                ->setLabel('Характеристики продукта')
                ->setLabelAttribute('icon', 'bell outline')
                ->setExtra('priority', 0);
        }
    }
}
