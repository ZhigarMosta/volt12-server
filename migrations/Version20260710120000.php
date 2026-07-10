<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710120000 extends AbstractMigration
{
    private const TABLES = ['catalog_items', 'catalogs', 'services'];

    public function getDescription(): string
    {
        return 'Add SEO metadata columns to catalog_items/catalogs/services, make slugs unique';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TABLES as $table) {
            $this->addSql("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS seo_meta_title VARCHAR(255) DEFAULT NULL");
            $this->addSql("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS seo_meta_description TEXT DEFAULT NULL");
            $this->addSql("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS seo_meta_keywords VARCHAR(255) DEFAULT NULL");
            $this->addSql("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS seo_noindex BOOLEAN NOT NULL DEFAULT false");
            $this->addSql("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS seo_nofollow BOOLEAN NOT NULL DEFAULT false");
            $this->addSql("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS seo_canonical_url VARCHAR(512) DEFAULT NULL");
        }

        // services.slug уже уникален (uniq_7332e169989d9b62); добавляем уникальность остальным
        $this->addSql('DROP INDEX IF EXISTS idx_catalog_items_slug');
        $this->addSql('CREATE UNIQUE INDEX idx_catalog_items_slug ON catalog_items (slug)');
        $this->addSql('DROP INDEX IF EXISTS idx_catalogs_slug');
        $this->addSql('CREATE UNIQUE INDEX idx_catalogs_slug ON catalogs (slug)');
    }

    public function down(Schema $schema): void
    {
        foreach (self::TABLES as $table) {
            $this->addSql("ALTER TABLE {$table} DROP COLUMN IF EXISTS seo_meta_title");
            $this->addSql("ALTER TABLE {$table} DROP COLUMN IF EXISTS seo_meta_description");
            $this->addSql("ALTER TABLE {$table} DROP COLUMN IF EXISTS seo_meta_keywords");
            $this->addSql("ALTER TABLE {$table} DROP COLUMN IF EXISTS seo_noindex");
            $this->addSql("ALTER TABLE {$table} DROP COLUMN IF EXISTS seo_nofollow");
            $this->addSql("ALTER TABLE {$table} DROP COLUMN IF EXISTS seo_canonical_url");
        }

        $this->addSql('DROP INDEX IF EXISTS idx_catalog_items_slug');
        $this->addSql('CREATE INDEX idx_catalog_items_slug ON catalog_items (slug)');
        $this->addSql('DROP INDEX IF EXISTS idx_catalogs_slug');
        $this->addSql('CREATE INDEX idx_catalogs_slug ON catalogs (slug)');
    }
}
