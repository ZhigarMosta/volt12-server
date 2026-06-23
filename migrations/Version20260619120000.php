<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create entity_history table for tracking entity changes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE entity_history (
                id SERIAL PRIMARY KEY,
                entity VARCHAR(255) NOT NULL,
                entity_id INTEGER NOT NULL,
                entity_class VARCHAR(512) NOT NULL,
                fields JSON NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT NOW()
            )
        ');

        $this->addSql('
            CREATE INDEX idx_entity_history_lookup ON entity_history (entity, entity_id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE entity_history');
    }
}
