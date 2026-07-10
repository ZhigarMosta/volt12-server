<?php

namespace App\Controller\Admin;

use App\Provider\ProductCodeProvider;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Кликабельная структура страниц сайта: статичные и служебные страницы,
 * категории, товары и услуги — со статусами индексации и ссылками
 * «открыть на сайте» / «редактировать в админке».
 */
#[Route('/admin/tools')]
class SiteStructureController extends AbstractController
{
    private const STATIC_PAGES = [
        ['title' => 'Главная', 'path' => '/'],
        ['title' => 'Каталог (все категории)', 'path' => '/catalog'],
        ['title' => 'Услуги (список)', 'path' => '/services'],
        ['title' => 'О нас', 'path' => '/about'],
        ['title' => 'Контакты', 'path' => '/contacts'],
        ['title' => 'Политика конфиденциальности', 'path' => '/privacy-policy'],
    ];

    private const SYSTEM_PAGES = [
        ['title' => 'Корзина', 'path' => '/cart'],
        ['title' => 'Оформление заказа', 'path' => '/checkout'],
        ['title' => 'Избранное', 'path' => '/favorites'],
        ['title' => 'Сравнение', 'path' => '/compare'],
        ['title' => 'Личный кабинет', 'path' => '/profile'],
        ['title' => 'История заказов (+ страница каждого заказа)', 'path' => '/orders'],
    ];

    public function __construct(
        private Connection $connection,
        private string $frontendUrl,
    ) {}

    #[Route('/site-structure', name: 'app_admin_site_structure', methods: ['GET'])]
    public function __invoke(): Response
    {
        $codes = [ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY];

        $catalogs = $this->connection->fetchAllAssociative(
            'SELECT id, name, slug, seo_noindex FROM catalogs
             WHERE product_code IN (:codes)
             ORDER BY position NULLS LAST, name',
            ['codes' => $codes],
            ['codes' => \Doctrine\DBAL\ArrayParameterType::STRING],
        );

        $items = $this->connection->fetchAllAssociative(
            'SELECT id, name, slug, is_published, seo_noindex, catalog_id FROM catalog_items
             WHERE product_code IN (:codes)
             ORDER BY name',
            ['codes' => $codes],
            ['codes' => \Doctrine\DBAL\ArrayParameterType::STRING],
        );

        $groups = $this->connection->fetchAllAssociative(
            'SELECT id, name FROM service_groups ORDER BY name',
        );

        $services = $this->connection->fetchAllAssociative(
            'SELECT id, name, slug, is_published, seo_noindex, service_group_id FROM services ORDER BY name',
        );

        // Товары по категориям
        $itemsByCatalog = [];
        foreach ($items as $item) {
            $itemsByCatalog[$item['catalog_id'] ?? 0][] = $item;
        }
        $catalogTree = [];
        foreach ($catalogs as $catalog) {
            $catalog['items'] = $itemsByCatalog[$catalog['id']] ?? [];
            unset($itemsByCatalog[$catalog['id']]);
            $catalogTree[] = $catalog;
        }
        $orphanItems = array_merge(...array_values($itemsByCatalog) ?: [[]]);

        // Услуги по группам
        $servicesByGroup = [];
        foreach ($services as $service) {
            $servicesByGroup[$service['service_group_id'] ?? 0][] = $service;
        }
        $groupTree = [];
        foreach ($groups as $group) {
            $group['services'] = $servicesByGroup[$group['id']] ?? [];
            unset($servicesByGroup[$group['id']]);
            $groupTree[] = $group;
        }
        $orphanServices = array_merge(...array_values($servicesByGroup) ?: [[]]);

        // Счётчики
        $publishedItems = array_filter($items, static fn (array $i): bool => (bool) $i['is_published']);
        $publishedServices = array_filter($services, static fn (array $s): bool => (bool) $s['is_published']);

        $indexable = count(self::STATIC_PAGES)
            + count(array_filter($catalogs, static fn (array $c): bool => !$c['seo_noindex']))
            + count(array_filter($publishedItems, static fn (array $i): bool => !$i['seo_noindex']))
            + count(array_filter($publishedServices, static fn (array $s): bool => !$s['seo_noindex']));

        $counts = [
            'total' => count(self::STATIC_PAGES) + count(self::SYSTEM_PAGES)
                + count($catalogs) + count($publishedItems) + count($publishedServices),
            'indexable' => $indexable,
            'catalogs' => count($catalogs),
            'items' => count($items),
            'items_hidden' => count($items) - count($publishedItems),
            'services' => count($services),
            'services_hidden' => count($services) - count($publishedServices),
            'static' => count(self::STATIC_PAGES),
            'system' => count(self::SYSTEM_PAGES),
        ];

        return $this->render('admin/tools/site_structure.html.twig', [
            'frontendUrl' => rtrim($this->frontendUrl, '/'),
            'staticPages' => self::STATIC_PAGES,
            'systemPages' => self::SYSTEM_PAGES,
            'catalogTree' => $catalogTree,
            'orphanItems' => $orphanItems,
            'groupTree' => $groupTree,
            'orphanServices' => $orphanServices,
            'counts' => $counts,
        ]);
    }
}
