<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509185840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE service_groups_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE services_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE service_groups (id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, product_code VARCHAR(255) NOT NULL, position INT DEFAULT NULL, imglink VARCHAR(2048) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE services (id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, short_description TEXT DEFAULT NULL, position INT DEFAULT NULL, imglink VARCHAR(2048) DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE service_groups_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE services_id_seq CASCADE');
        $this->addSql('DROP TABLE service_groups');
        $this->addSql('DROP TABLE services');
    }
}
