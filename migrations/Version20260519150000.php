<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260519150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add short_description and count to catalog_items';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE catalog_items ADD short_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE catalog_items ADD count INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE catalog_items DROP short_description');
        $this->addSql('ALTER TABLE catalog_items DROP count');
    }
}
