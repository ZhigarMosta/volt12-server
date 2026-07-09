<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260708150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add in_footer column to services';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE services ADD COLUMN IF NOT EXISTS in_footer BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_services_in_footer ON services (in_footer)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_services_in_footer');
        $this->addSql('ALTER TABLE services DROP COLUMN IF EXISTS in_footer');
    }
}
