<?php

namespace App\Menu;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $this->removeDefaultItems($menu);

        // ===== Каталог =====
        $catalog = $menu->getChild('catalog') ?? $menu->addChild('catalog');
        $catalog
            ->setLabel('Каталог')
            ->setLabelAttribute('icon', 'tabler:cube');

        $this->item($catalog, 'catalog_list', 'app_admin_catalog_index', 'Каталоги');
        $this->item($catalog, 'catalog_group', 'app_admin_catalog_group_index', 'Группы каталогов');
        $this->item($catalog, 'catalog_item', 'app_admin_catalog_item_index', 'Продукты');
        $this->item($catalog, 'catalog_item_image', 'app_admin_catalog_item_image_index', 'Изображения продуктов');
        $this->item($catalog, 'catalog_characteristic', 'app_admin_catalog_characteristic_index', 'Характеристики каталога');
        $this->item($catalog, 'catalog_item_characteristic', 'app_admin_catalog_item_characteristic_index', 'Характеристики продукта');

        // ===== Услуги =====
        $services = $menu->addChild('app_services')
            ->setLabel('Услуги')
            ->setLabelAttribute('icon', 'tabler:briefcase');

        $this->item($services, 'service_group', 'app_admin_service_group_index', 'Группы услуг');
        $this->item($services, 'service', 'app_admin_service_index', 'Услуги');

        // ===== Продажи =====
        $sales = $menu->addChild('app_sales')
            ->setLabel('Продажи')
            ->setLabelAttribute('icon', 'tabler:shopping-cart');

        $this->item($sales, 'user_order', 'app_admin_user_order_index', 'Заказы');
        $this->item($sales, 'user_order_item', 'app_admin_user_order_item_index', 'Позиции заказов');
        $this->item($sales, 'cart', 'app_admin_cart_index', 'Корзины');

        // ===== Клиенты =====
        $customers = $menu->addChild('app_customers')
            ->setLabel('Клиенты')
            ->setLabelAttribute('icon', 'tabler:users');

        $this->item($customers, 'client_users', 'app_admin_user_index', 'Клиенты');
        $this->item($customers, 'favorites', 'app_admin_favorite_index', 'Избранное');
        $this->item($customers, 'compare', 'app_admin_compare_index', 'Сравнения');

        // ===== Отзывы =====
        $reviews = $menu->addChild('app_reviews')
            ->setLabel('Отзывы')
            ->setLabelAttribute('icon', 'tabler:star');

        $this->item($reviews, 'feedback_from_map', 'app_admin_feedback_from_map_index', 'Отзывы с карт');

        // ===== SEO =====
        $seo = $menu->addChild('app_seo')
            ->setLabel('SEO')
            ->setLabelAttribute('icon', 'tabler:world-search');

        $this->item($seo, 'robots_txt', 'app_admin_robots_txt', 'robots.txt');

        // ===== Система =====
        $system = $menu->addChild('app_system')
            ->setLabel('Система')
            ->setLabelAttribute('icon', 'tabler:settings');

        $this->item($system, 'entity_history', 'app_admin_entity_history_index', 'История изменений');
        $this->item($system, 'user_token', 'app_admin_user_token_index', 'Токены');
        $this->item($system, 'er_diagram', 'app_admin_er_diagram', 'ER-диаграмма БД');
        $this->item($system, 'site_structure', 'app_admin_site_structure', 'Структура сайта');
        $this->item($system, 'images_check', 'app_admin_images_check', 'Проверка картинок');
        $this->item($system, 'docs', 'app_admin_docs', 'Документация');
    }

    private function item(ItemInterface $parent, string $key, string $route, string $label): void
    {
        $parent->addChild($key, ['route' => $route])->setLabel($label);
    }

    private function removeDefaultItems(ItemInterface $menu): void
    {
        if (null !== $catalog = $menu->getChild('catalog')) {
            $catalog->removeChild('taxons');
            $catalog->removeChild('products');
            $catalog->removeChild('inventory');
            $catalog->removeChild('attributes');
            $catalog->removeChild('options');
            $catalog->removeChild('association_types');
        }

        foreach (['sales', 'customer', 'marketing', 'customers', 'configuration', 'official_support', 'sylius.ui.administration'] as $child) {
            $menu->removeChild($child);
        }
    }
}
