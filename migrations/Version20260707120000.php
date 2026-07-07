<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260707120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add remaining missing indexes on catalogs, catalog_items and services';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_catalogs_slug ON catalogs (slug)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_position ON catalogs (position)');

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_catalog_items_name ON catalog_items (name)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_catalog_items_price ON catalog_items (price)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_catalog_items_is_published ON catalog_items (is_published)');

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_services_position ON services (position)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_services_name ON services (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_catalogs_slug');
        $this->addSql('DROP INDEX IF EXISTS idx_position');

        $this->addSql('DROP INDEX IF EXISTS idx_catalog_items_name');
        $this->addSql('DROP INDEX IF EXISTS idx_catalog_items_price');
        $this->addSql('DROP INDEX IF EXISTS idx_catalog_items_is_published');

        $this->addSql('DROP INDEX IF EXISTS idx_services_position');
        $this->addSql('DROP INDEX IF EXISTS idx_services_name');
    }
}
