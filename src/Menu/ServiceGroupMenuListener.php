<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class ServiceGroupMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog
                ->addChild('service_group', ['route' => 'app_admin_service_group_index'])
                ->setLabel('Группы услуг')
                ->setLabelAttribute('icon', 'bell outline')
                ->setExtra('priority', 0);
        }
    }
}
