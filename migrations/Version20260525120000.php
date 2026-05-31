<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260525120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create favorites table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE favorites_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE favorites (id INT NOT NULL, user_id INT NOT NULL, catalog_item_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E46960F1A76ED395 ON favorites (user_id)');
        $this->addSql('CREATE INDEX IDX_E46960F11DDDAF72 ON favorites (catalog_item_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_favorites_user_catalog_item ON favorites (user_id, catalog_item_id)');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorites ADD CONSTRAINT FK_E46960F11DDDAF72 FOREIGN KEY (catalog_item_id) REFERENCES catalog_items (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE favorites_id_seq CASCADE');
        $this->addSql('ALTER TABLE favorites DROP CONSTRAINT FK_E46960F1A76ED395');
        $this->addSql('ALTER TABLE favorites DROP CONSTRAINT FK_E46960F11DDDAF72');
        $this->addSql('DROP TABLE favorites');
    }
}
