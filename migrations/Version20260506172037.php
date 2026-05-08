<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260506172037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE catalog_characteristics ADD COLUMN IF NOT EXISTS product_code VARCHAR(255) DEFAULT \'\'');
        $this->addSql('UPDATE catalog_characteristics SET product_code = \'volt12\' WHERE product_code = \'\' OR product_code IS NULL');
        $this->addSql('ALTER TABLE catalog_characteristics ALTER COLUMN product_code SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE catalog_characteristics DROP COLUMN IF EXISTS product_code');
    }
}
