<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260709120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_published column to services';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE services ADD COLUMN IF NOT EXISTS is_published BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_services_is_published ON services (is_published)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_services_is_published');
        $this->addSql('ALTER TABLE services DROP COLUMN IF EXISTS is_published');
    }
}
