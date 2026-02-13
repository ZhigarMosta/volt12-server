<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213150942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE group_characteristics_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE catalog_groups_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE catalog_groups (id INT NOT NULL, catalog_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_42692139CC3C66FC ON catalog_groups (catalog_id)');
        $this->addSql('ALTER TABLE catalog_groups ADD CONSTRAINT FK_42692139CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_characteristics DROP CONSTRAINT fk_cea42c8f8eaab78b');
        $this->addSql('ALTER TABLE group_characteristics DROP CONSTRAINT fk_cea42c8fcc3c66fc');
        $this->addSql('DROP TABLE group_characteristics');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE catalog_groups_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE group_characteristics_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE group_characteristics (id INT NOT NULL, catalog_id INT NOT NULL, catalog_characteristic_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_cea42c8f8eaab78b ON group_characteristics (catalog_characteristic_id)');
        $this->addSql('CREATE INDEX idx_cea42c8fcc3c66fc ON group_characteristics (catalog_id)');
        $this->addSql('ALTER TABLE group_characteristics ADD CONSTRAINT fk_cea42c8f8eaab78b FOREIGN KEY (catalog_characteristic_id) REFERENCES catalog_characteristics (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE group_characteristics ADD CONSTRAINT fk_cea42c8fcc3c66fc FOREIGN KEY (catalog_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_groups DROP CONSTRAINT FK_42692139CC3C66FC');
        $this->addSql('DROP TABLE catalog_groups');
    }
}
