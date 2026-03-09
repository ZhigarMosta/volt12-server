<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308171808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_from_map ALTER message TYPE VARCHAR(2048)');
        $this->addSql('ALTER TABLE feedback_from_map ALTER feedback_link TYPE VARCHAR(2048)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_from_map ALTER message TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE feedback_from_map ALTER feedback_link TYPE TEXT');
        $this->addSql('ALTER TABLE feedback_from_map ALTER feedback_link TYPE TEXT');
    }
}
