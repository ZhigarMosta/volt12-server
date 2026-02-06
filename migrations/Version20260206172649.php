<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206172649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE group_characteristics_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE group_characteristics (id INT NOT NULL, catalog_id INT NOT NULL, catalog_characteristic_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CEA42C8FCC3C66FC ON group_characteristics (catalog_id)');
        $this->addSql('CREATE INDEX IDX_CEA42C8F8EAAB78B ON group_characteristics (catalog_characteristic_id)');
        $this->addSql('ALTER TABLE group_characteristics ADD CONSTRAINT FK_CEA42C8FCC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_characteristics ADD CONSTRAINT FK_CEA42C8F8EAAB78B FOREIGN KEY (catalog_characteristic_id) REFERENCES catalog_characteristics (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT FK_CFFCEBCF8EAAB78B');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT FK_CFFCEBCF8EAAB78B FOREIGN KEY (catalog_characteristic_id) REFERENCES catalog_characteristics (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE group_characteristics_id_seq CASCADE');
        $this->addSql('ALTER TABLE group_characteristics DROP CONSTRAINT FK_CEA42C8FCC3C66FC');
        $this->addSql('ALTER TABLE group_characteristics DROP CONSTRAINT FK_CEA42C8F8EAAB78B');
        $this->addSql('DROP TABLE group_characteristics');
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT fk_cffcebcf8eaab78b');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT fk_cffcebcf8eaab78b FOREIGN KEY (catalog_characteristic_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
