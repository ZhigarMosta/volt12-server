<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509194132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE services ADD service_group_id INT DEFAULT NULL');
        $this->addSql('UPDATE services SET service_group_id = (SELECT id FROM service_groups LIMIT 1) WHERE service_group_id IS NULL');
        $this->addSql('ALTER TABLE services ALTER service_group_id SET NOT NULL');
        $this->addSql('ALTER TABLE services ADD CONSTRAINT FK_7332E169722827A FOREIGN KEY (service_group_id) REFERENCES service_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7332E169722827A ON services (service_group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE services DROP CONSTRAINT FK_7332E169722827A');
        $this->addSql('DROP INDEX IDX_7332E169722827A');
        $this->addSql('ALTER TABLE services DROP service_group_id');
    }
}
