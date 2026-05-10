<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class ServiceMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog
                ->addChild('service', ['route' => 'app_admin_service_index'])
                ->setLabel('Услуги')
                ->setLabelAttribute('icon', 'bell outline')
                ->setExtra('priority', 0);
        }
    }
}
