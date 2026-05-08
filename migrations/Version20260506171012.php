<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260506171012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add position fields to catalog_characteristics and catalog_groups, drop product_code from catalog_characteristics and catalog_item_images';
    }

    public function up(Schema $schema): void
    {
        // Add position to catalog_characteristics if not exists
        $this->addSql('ALTER TABLE catalog_characteristics ADD COLUMN IF NOT EXISTS position INT DEFAULT NULL');

        // Drop product_code from catalog_characteristics if exists
        $this->addSql('ALTER TABLE catalog_characteristics DROP COLUMN IF EXISTS product_code');

        // Add position to catalog_groups if not exists
        $this->addSql('ALTER TABLE catalog_groups ADD COLUMN IF NOT EXISTS position INT DEFAULT NULL');

        // Drop product_code from catalog_item_images if exists
        $this->addSql('ALTER TABLE catalog_item_images DROP COLUMN IF EXISTS product_code');
    }

    public function down(Schema $schema): void
    {
        // Reverse changes
        $this->addSql('ALTER TABLE catalog_characteristics ADD COLUMN IF NOT EXISTS product_code VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE catalog_characteristics DROP COLUMN IF EXISTS position');

        $this->addSql('ALTER TABLE catalog_groups DROP COLUMN IF EXISTS position');

        $this->addSql('ALTER TABLE catalog_item_images ADD COLUMN IF NOT EXISTS product_code VARCHAR(255) NOT NULL');
    }
}
