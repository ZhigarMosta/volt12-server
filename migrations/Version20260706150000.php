<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260706150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing indexes on self-written entities (FK columns and fields used in WHERE clauses)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_catalogs_product_code ON catalogs (product_code)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_catalogs_is_popular ON catalogs (is_popular)');

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_catalog_items_slug ON catalog_items (slug)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_catalog_items_product_code ON catalog_items (product_code)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_catalog_items_is_popular ON catalog_items (is_popular)');

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_catalog_characteristics_product_code ON catalog_characteristics (product_code)');

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_feedback_from_map_product_code ON feedback_from_map (product_code)');

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_user_orders_user_created ON user_orders (user_id, created_at)');

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_user_order_items_order_id ON user_order_items (order_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_user_order_items_catalog_item_id ON user_order_items (catalog_item_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_catalogs_product_code');
        $this->addSql('DROP INDEX IF EXISTS idx_catalogs_is_popular');

        $this->addSql('DROP INDEX IF EXISTS idx_catalog_items_slug');
        $this->addSql('DROP INDEX IF EXISTS idx_catalog_items_product_code');
        $this->addSql('DROP INDEX IF EXISTS idx_catalog_items_is_popular');

        $this->addSql('DROP INDEX IF EXISTS idx_catalog_characteristics_product_code');

        $this->addSql('DROP INDEX IF EXISTS idx_feedback_from_map_product_code');

        $this->addSql('DROP INDEX IF EXISTS idx_user_orders_user_created');

        $this->addSql('DROP INDEX IF EXISTS idx_user_order_items_order_id');
        $this->addSql('DROP INDEX IF EXISTS idx_user_order_items_catalog_item_id');
    }
}
