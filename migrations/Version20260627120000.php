<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

/**
 * Удаляет неиспользуемые таблицы Sylius (товары, заказы, акции, доставки, платежи,
 * налоги, покупатели/shop-users), плагинов (Mollie/PayPal) и messenger.
 *
 * СОХРАНЯЕТСЯ минимально необходимая инфраструктура Sylius, которую ядро запрашивает
 * на каждый запрос (контекст канала/локали/валюты/гео) — без неё падает даже логин:
 *   sylius_channel(+join), sylius_locale, sylius_currency, sylius_exchange_rate,
 *   sylius_country, sylius_province, sylius_zone(+member), sylius_taxon(+tr/img),
 *   а также sylius_admin_user (вход), sylius_avatar_image (аватар), sylius_migrations.
 *
 * ORM-маппинг сущностей Sylius сохраняется, поэтому при необходимости схему можно
 * восстановить через `doctrine:schema:update --force`.
 */
final class Version20260627120000 extends AbstractMigration
{
    private const TABLES_TO_DROP = [
        // messenger (транспорты отключены)
        'messenger_messages',
        // Mollie plugin
        'mollie_configuration',
        'mollie_configuration_amount_limits',
        'mollie_configuration_surcharge_fee',
        'mollie_configuration_translation',
        'mollie_customer',
        'mollie_email_template',
        'mollie_email_template_translation',
        'mollie_logger',
        'mollie_method_image',
        'mollie_product_type',
        'mollie_subscription',
        'mollie_subscription_configuration',
        'mollie_subscription_orders',
        'mollie_subscription_payments',
        'mollie_subscription_schedule',
        // PayPal plugin
        'sylius_paypal_plugin_pay_pal_credentials',
        // Адреса (addressing) — сами заказы/клиенты удаляем
        'sylius_address',
        'sylius_address_log_entries',
        // Покупатели и shop-пользователи
        'sylius_customer',
        'sylius_customer_group',
        'sylius_shop_user',
        'sylius_shop_billing_data',
        'sylius_user_oauth',
        // Заказы / корзины Sylius / суммы
        'sylius_adjustment',
        'sylius_order',
        'sylius_order_item',
        'sylius_order_item_unit',
        'sylius_order_sequence',
        // Платежи
        'sylius_payment',
        'sylius_payment_method',
        'sylius_payment_method_channels',
        'sylius_payment_method_translation',
        'sylius_payment_request',
        'sylius_payment_security_token',
        'sylius_gateway_config',
        // Акции и каталожные акции
        'sylius_catalog_promotion',
        'sylius_catalog_promotion_action',
        'sylius_catalog_promotion_channels',
        'sylius_catalog_promotion_scope',
        'sylius_catalog_promotion_translation',
        'sylius_promotion',
        'sylius_promotion_action',
        'sylius_promotion_channels',
        'sylius_promotion_coupon',
        'sylius_promotion_order',
        'sylius_promotion_rule',
        'sylius_promotion_translation',
        // Ценообразование по каналам (привязано к вариантам товаров)
        'sylius_channel_pricing',
        'sylius_channel_pricing_catalog_promotions',
        'sylius_channel_pricing_log_entry',
        // Товары / атрибуты / опции / варианты
        'sylius_product',
        'sylius_product_association',
        'sylius_product_association_product',
        'sylius_product_association_type',
        'sylius_product_association_type_translation',
        'sylius_product_attribute',
        'sylius_product_attribute_translation',
        'sylius_product_attribute_value',
        'sylius_product_channels',
        'sylius_product_image',
        'sylius_product_image_product_variants',
        'sylius_product_option',
        'sylius_product_option_translation',
        'sylius_product_option_value',
        'sylius_product_option_value_translation',
        'sylius_product_options',
        'sylius_product_review',
        'sylius_product_taxon',
        'sylius_product_translation',
        'sylius_product_variant',
        'sylius_product_variant_option_value',
        'sylius_product_variant_translation',
        // Доставки
        'sylius_shipment',
        'sylius_shipping_category',
        'sylius_shipping_method',
        'sylius_shipping_method_channels',
        'sylius_shipping_method_rule',
        'sylius_shipping_method_translation',
        // Налоги
        'sylius_tax_category',
        'sylius_tax_rate',
    ];

    public function getDescription(): string
    {
        return 'Удаление неиспользуемых таблиц Sylius (товары/заказы/акции/доставки/платежи/налоги/покупатели), плагинов и messenger. Инфраструктура канала/локали/валюты/гео и админ-пользователь сохранены.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQLPlatform,
            'Миграция рассчитана на PostgreSQL (используется DROP ... CASCADE).',
        );

        foreach (self::TABLES_TO_DROP as $table) {
            $this->addSql(sprintf('DROP TABLE IF EXISTS "%s" CASCADE', $table));
        }
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration(
            'Откат невозможен. Схему таблиц Sylius можно пересоздать командой "doctrine:schema:update --force" '
            . '(ORM-маппинг сущностей Sylius сохранён).',
        );
    }
}
