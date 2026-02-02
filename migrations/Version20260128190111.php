<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260128190111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE catalogs_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE catalogs (id INT NOT NULL, name VARCHAR(255) NOT NULL, product_code VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE catalog_items ADD catalog_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE catalog_items ADD CONSTRAINT FK_580D88F4CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_580D88F4CC3C66FC ON catalog_items (catalog_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_items DROP CONSTRAINT FK_580D88F4CC3C66FC');
        $this->addSql('DROP SEQUENCE catalogs_id_seq CASCADE');
        $this->addSql('DROP TABLE catalogs');
        $this->addSql('DROP INDEX IDX_580D88F4CC3C66FC');
        $this->addSql('ALTER TABLE catalog_items DROP catalog_id');
    }
}
