<?php

namespace App\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $catalog = $menu->getChild('catalog');

        if ($catalog) {
            $catalog->removeChild('taxons');
            $catalog->removeChild('products');
            $catalog->removeChild('inventory');
            $catalog->removeChild('attributes');
            $catalog->removeChild('options');
            $catalog->removeChild('association_types');
        }

        $menu->removeChild('sales');
        $menu->removeChild('customer');
        $menu->removeChild('marketing');
        $menu->removeChild('customers');
        $menu->removeChild('configuration');
        $menu->removeChild('official_support');
        $menu->removeChild('sylius.ui.administration');
    }
}
