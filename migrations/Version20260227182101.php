<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227182101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_items ADD is_published BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE catalog_items DROP is_publiched');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_items ADD is_publiched BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE catalog_items DROP is_published');
    }
}
