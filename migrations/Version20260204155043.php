<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204155043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE category_characteristics_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE catalog_characteristics_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE catalog_characteristics (id INT NOT NULL, catalog_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E15B8F9CC3C66FC ON catalog_characteristics (catalog_id)');
        $this->addSql('ALTER TABLE catalog_characteristics ADD CONSTRAINT FK_E15B8F9CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category_characteristics DROP CONSTRAINT fk_28beed04cc3c66fc');
        $this->addSql('DROP TABLE category_characteristics');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE catalog_characteristics_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE category_characteristics_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category_characteristics (id INT NOT NULL, catalog_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_28beed04cc3c66fc ON category_characteristics (catalog_id)');
        $this->addSql('ALTER TABLE category_characteristics ADD CONSTRAINT fk_28beed04cc3c66fc FOREIGN KEY (catalog_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_characteristics DROP CONSTRAINT FK_E15B8F9CC3C66FC');
        $this->addSql('DROP TABLE catalog_characteristics');
    }
}
