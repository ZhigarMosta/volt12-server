<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302154930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalogs ADD position INT DEFAULT NULL');
        $this->addSql('ALTER TABLE catalogs ADD is_popular BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE catalogs ALTER product_code DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalogs DROP position');
        $this->addSql('ALTER TABLE catalogs DROP is_popular');
        $this->addSql('ALTER TABLE catalogs ALTER product_code SET NOT NULL');
    }
}
