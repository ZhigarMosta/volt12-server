<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215161918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_group_characteristic DROP CONSTRAINT fk_cfa8e119cc3c66fc');
        $this->addSql('DROP INDEX idx_cfa8e119cc3c66fc');
        $this->addSql('ALTER TABLE catalog_group_characteristic RENAME COLUMN catalog_id TO catalog_characteristic_id');
        $this->addSql('ALTER TABLE catalog_group_characteristic ADD CONSTRAINT FK_CFA8E1198EAAB78B FOREIGN KEY (catalog_characteristic_id) REFERENCES catalog_characteristics (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CFA8E1198EAAB78B ON catalog_group_characteristic (catalog_characteristic_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_group_characteristic DROP CONSTRAINT FK_CFA8E1198EAAB78B');
        $this->addSql('DROP INDEX IDX_CFA8E1198EAAB78B');
        $this->addSql('ALTER TABLE catalog_group_characteristic RENAME COLUMN catalog_characteristic_id TO catalog_id');
        $this->addSql('ALTER TABLE catalog_group_characteristic ADD CONSTRAINT fk_cfa8e119cc3c66fc FOREIGN KEY (catalog_id) REFERENCES catalog_characteristics (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_cfa8e119cc3c66fc ON catalog_group_characteristic (catalog_id)');
    }
}
