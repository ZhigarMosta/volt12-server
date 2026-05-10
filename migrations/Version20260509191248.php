<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509191248 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_groups RENAME COLUMN imglink TO img_link');
        $this->addSql('ALTER TABLE services RENAME COLUMN imglink TO img_link');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service_groups RENAME COLUMN img_link TO imglink');
        $this->addSql('ALTER TABLE services RENAME COLUMN img_link TO imglink');
    }
}
