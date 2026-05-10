<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class CartMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog
                ->addChild('cart', ['route' => 'app_admin_cart_index'])
                ->setLabel('Корзины')
                ->setLabelAttribute('icon', 'shopping cart')
                ->setExtra('priority', 0);
        }
    }
}
