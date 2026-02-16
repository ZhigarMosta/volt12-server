<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215153913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE catalog_group_characteristic_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE catalog_group_characteristic (id INT NOT NULL, catalog_group_id INT NOT NULL, catalog_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CFA8E119CC830759 ON catalog_group_characteristic (catalog_group_id)');
        $this->addSql('CREATE INDEX IDX_CFA8E119CC3C66FC ON catalog_group_characteristic (catalog_id)');
        $this->addSql('ALTER TABLE catalog_group_characteristic ADD CONSTRAINT FK_CFA8E119CC830759 FOREIGN KEY (catalog_group_id) REFERENCES catalog_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_group_characteristic ADD CONSTRAINT FK_CFA8E119CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog_characteristics (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_characteristics DROP CONSTRAINT FK_E15B8F9CC3C66FC');
        $this->addSql('ALTER TABLE catalog_characteristics ADD CONSTRAINT FK_E15B8F9CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_groups DROP CONSTRAINT FK_42692139CC3C66FC');
        $this->addSql('ALTER TABLE catalog_groups ADD CONSTRAINT FK_42692139CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT FK_CFFCEBCF1DDDAF72');
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT FK_CFFCEBCF8EAAB78B');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT FK_CFFCEBCF1DDDAF72 FOREIGN KEY (catalog_item_id) REFERENCES catalog_items (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT FK_CFFCEBCF8EAAB78B FOREIGN KEY (catalog_characteristic_id) REFERENCES catalog_characteristics (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_items DROP CONSTRAINT FK_580D88F4CC3C66FC');
        $this->addSql('ALTER TABLE catalog_items ADD CONSTRAINT FK_580D88F4CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE catalog_group_characteristic_id_seq CASCADE');
        $this->addSql('ALTER TABLE catalog_group_characteristic DROP CONSTRAINT FK_CFA8E119CC830759');
        $this->addSql('ALTER TABLE catalog_group_characteristic DROP CONSTRAINT FK_CFA8E119CC3C66FC');
        $this->addSql('DROP TABLE catalog_group_characteristic');
        $this->addSql('ALTER TABLE catalog_items DROP CONSTRAINT fk_580d88f4cc3c66fc');
        $this->addSql('ALTER TABLE catalog_items ADD CONSTRAINT fk_580d88f4cc3c66fc FOREIGN KEY (catalog_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT fk_cffcebcf1dddaf72');
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT fk_cffcebcf8eaab78b');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT fk_cffcebcf1dddaf72 FOREIGN KEY (catalog_item_id) REFERENCES catalog_items (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT fk_cffcebcf8eaab78b FOREIGN KEY (catalog_characteristic_id) REFERENCES catalog_characteristics (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_groups DROP CONSTRAINT fk_42692139cc3c66fc');
        $this->addSql('ALTER TABLE catalog_groups ADD CONSTRAINT fk_42692139cc3c66fc FOREIGN KEY (catalog_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_characteristics DROP CONSTRAINT fk_e15b8f9cc3c66fc');
        $this->addSql('ALTER TABLE catalog_characteristics ADD CONSTRAINT fk_e15b8f9cc3c66fc FOREIGN KEY (catalog_id) REFERENCES catalogs (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
