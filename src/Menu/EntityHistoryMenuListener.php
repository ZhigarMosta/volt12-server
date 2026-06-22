<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class EntityHistoryMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $menu
            ->addChild('entity_history', ['route' => 'app_admin_entity_history_index'])
            ->setLabel('История изменений')
            ->setLabelAttribute('icon', 'clock outline')
            ->setExtra('priority', -100);
    }
}
