<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204175404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE catalog_item_characteristics_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE catalog_item_characteristics (id INT NOT NULL, catalog_item_id INT NOT NULL, catalog_characteristic_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CFFCEBCF1DDDAF72 ON catalog_item_characteristics (catalog_item_id)');
        $this->addSql('CREATE INDEX IDX_CFFCEBCF8EAAB78B ON catalog_item_characteristics (catalog_characteristic_id)');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT FK_CFFCEBCF1DDDAF72 FOREIGN KEY (catalog_item_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT FK_CFFCEBCF8EAAB78B FOREIGN KEY (catalog_characteristic_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_characteristics ALTER catalog_id SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE catalog_item_characteristics_id_seq CASCADE');
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT FK_CFFCEBCF1DDDAF72');
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT FK_CFFCEBCF8EAAB78B');
        $this->addSql('DROP TABLE catalog_item_characteristics');
        $this->addSql('ALTER TABLE catalog_characteristics ALTER catalog_id DROP NOT NULL');
    }
}
