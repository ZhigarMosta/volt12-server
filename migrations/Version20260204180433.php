<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204180433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT FK_CFFCEBCF1DDDAF72');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT FK_CFFCEBCF1DDDAF72 FOREIGN KEY (catalog_item_id) REFERENCES catalog_items (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT fk_cffcebcf1dddaf72');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT fk_cffcebcf1dddaf72 FOREIGN KEY (catalog_item_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
