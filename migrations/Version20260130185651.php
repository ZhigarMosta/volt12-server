<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130185651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_items ADD img_link VARCHAR(2048) NOT NULL');
        $this->addSql('ALTER TABLE catalog_items ADD product_code VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE catalog_items ADD is_new BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE catalog_items ADD is_popular BOOLEAN NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_items DROP img_link');
        $this->addSql('ALTER TABLE catalog_items DROP product_code');
        $this->addSql('ALTER TABLE catalog_items DROP is_new');
        $this->addSql('ALTER TABLE catalog_items DROP is_popular');
    }
}
