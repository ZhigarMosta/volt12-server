<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class UserMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog
                ->addChild('client_users', ['route' => 'app_admin_user_index'])
                ->setLabel('Клиенты')
                ->setLabelAttribute('icon', 'user outline')
                ->setExtra('priority', 0);
        }
    }
}
