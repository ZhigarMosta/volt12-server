<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204152702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE category_characteristics_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category_characteristics (id INT NOT NULL, catalog_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_28BEED04CC3C66FC ON category_characteristics (catalog_id)');
        $this->addSql('ALTER TABLE category_characteristics ADD CONSTRAINT FK_28BEED04CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE category_characteristics_id_seq CASCADE');
        $this->addSql('ALTER TABLE category_characteristics DROP CONSTRAINT FK_28BEED04CC3C66FC');
        $this->addSql('DROP TABLE category_characteristics');
    }
}
