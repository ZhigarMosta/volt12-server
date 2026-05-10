<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class CompareMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog
                ->addChild('compare', ['route' => 'app_admin_compare_index'])
                ->setLabel('Сравнения')
                ->setLabelAttribute('icon', 'balance scale')
                ->setExtra('priority', 0);
        }
    }
}
