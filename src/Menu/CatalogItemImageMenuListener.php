<?php

namespace App\Menu;

use App\Entity\CatalogItemImage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class CatalogItemImageMenuListener
{
    public function addToMain(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog
                ->addChild('catalog_item_image', ['route' => 'app_admin_catalog_item_image_index'])
                ->setLabel('Изоображения продукта')
                ->setLabelAttribute('icon', 'bell outline')
                ->setExtra('priority', 0);
        }
    }
}
