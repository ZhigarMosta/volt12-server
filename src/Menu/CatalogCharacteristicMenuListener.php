<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class CatalogCharacteristicMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog
                ->addChild('catalog_characteristic', ['route' => 'app_admin_catalog_characteristic_index'])
                ->setLabel('Характеристики каталога')
                ->setLabelAttribute('icon', 'bell outline')
                ->setExtra('priority', 0);
        }
    }
}
