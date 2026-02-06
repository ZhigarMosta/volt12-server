<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class GroupCharacteristicMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog
                ->addChild('group_characteristic', ['route' => 'app_admin_group_characteristic_index'])
                ->setLabel('Группа Характеристики')
                ->setLabelAttribute('icon', 'bell outline')
                ->setExtra('priority', 0);
        }
    }
}
