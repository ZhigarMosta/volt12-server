<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260611090819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add type column to user_tokens table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user_tokens ADD COLUMN type VARCHAR(32) NOT NULL DEFAULT 'auth'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_tokens DROP COLUMN type');
    }
}
