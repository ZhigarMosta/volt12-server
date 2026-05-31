<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class FavoriteMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog
                ->addChild('favorites', ['route' => 'app_admin_favorite_index'])
                ->setLabel('Избранное')
                ->setLabelAttribute('icon', 'heart')
                ->setExtra('priority', 0);
        }
    }
}
