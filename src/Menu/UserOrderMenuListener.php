<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class UserOrderMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $menu
            ->addChild('user_orders_', ['route' => 'app_admin_user_order_index'])
            ->setLabel('Заказы')
            ->setLabelAttribute('icon', 'shop')
            ->setExtra('priority', 10);
    }
}
