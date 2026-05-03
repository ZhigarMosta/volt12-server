<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260502153326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE catalog_characteristics_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE catalog_groups_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE catalog_item_characteristics_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE catalog_item_images_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE catalog_items_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE catalogs_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE feedback_from_map_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE catalog_characteristics (id INT NOT NULL, catalog_id INT NOT NULL, catalog_group_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, product_code VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E15B8F9CC3C66FC ON catalog_characteristics (catalog_id)');
        $this->addSql('CREATE INDEX IDX_E15B8F9CC830759 ON catalog_characteristics (catalog_group_id)');
        $this->addSql('CREATE TABLE catalog_groups (id INT NOT NULL, catalog_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_42692139CC3C66FC ON catalog_groups (catalog_id)');
        $this->addSql('CREATE TABLE catalog_item_characteristics (id INT NOT NULL, catalog_item_id INT NOT NULL, catalog_characteristic_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CFFCEBCF1DDDAF72 ON catalog_item_characteristics (catalog_item_id)');
        $this->addSql('CREATE INDEX IDX_CFFCEBCF8EAAB78B ON catalog_item_characteristics (catalog_characteristic_id)');
        $this->addSql('CREATE TABLE catalog_item_images (id INT NOT NULL, catalog_item_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, alt VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, position INT NOT NULL, img_link VARCHAR(2048) NOT NULL, product_code VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7752EA0B1DDDAF72 ON catalog_item_images (catalog_item_id)');
        $this->addSql('CREATE TABLE catalog_items (id INT NOT NULL, catalog_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, name VARCHAR(255) NOT NULL, price INT NOT NULL, position INT DEFAULT NULL, product_code VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, is_new BOOLEAN NOT NULL, is_popular BOOLEAN NOT NULL, is_published BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_580D88F4CC3C66FC ON catalog_items (catalog_id)');
        $this->addSql('CREATE TABLE catalogs (id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, product_code VARCHAR(255) DEFAULT NULL, position INT DEFAULT NULL, is_popular BOOLEAN DEFAULT NULL, imglink VARCHAR(2048) DEFAULT NULL, imgAlt VARCHAR(255) DEFAULT NULL, imgTitle VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE feedback_from_map (id INT NOT NULL, user_name VARCHAR(255) NOT NULL, map VARCHAR(255) DEFAULT NULL, product_code VARCHAR(255) DEFAULT NULL, position INT DEFAULT NULL, star_count INT NOT NULL, message VARCHAR(2048) NOT NULL, feedback_link VARCHAR(2048) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE catalog_characteristics ADD CONSTRAINT FK_E15B8F9CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_characteristics ADD CONSTRAINT FK_E15B8F9CC830759 FOREIGN KEY (catalog_group_id) REFERENCES catalog_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_groups ADD CONSTRAINT FK_42692139CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT FK_CFFCEBCF1DDDAF72 FOREIGN KEY (catalog_item_id) REFERENCES catalog_items (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_item_characteristics ADD CONSTRAINT FK_CFFCEBCF8EAAB78B FOREIGN KEY (catalog_characteristic_id) REFERENCES catalog_characteristics (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_item_images ADD CONSTRAINT FK_7752EA0B1DDDAF72 FOREIGN KEY (catalog_item_id) REFERENCES catalog_items (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_items ADD CONSTRAINT FK_580D88F4CC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalogs (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX idx_75ea56e016ba31db');
        $this->addSql('DROP INDEX idx_75ea56e0e3bd61ce');
        $this->addSql('DROP INDEX idx_75ea56e0fb7336f0');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE catalog_characteristics_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE catalog_groups_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE catalog_item_characteristics_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE catalog_item_images_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE catalog_items_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE catalogs_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE feedback_from_map_id_seq CASCADE');
        $this->addSql('ALTER TABLE catalog_characteristics DROP CONSTRAINT FK_E15B8F9CC3C66FC');
        $this->addSql('ALTER TABLE catalog_characteristics DROP CONSTRAINT FK_E15B8F9CC830759');
        $this->addSql('ALTER TABLE catalog_groups DROP CONSTRAINT FK_42692139CC3C66FC');
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT FK_CFFCEBCF1DDDAF72');
        $this->addSql('ALTER TABLE catalog_item_characteristics DROP CONSTRAINT FK_CFFCEBCF8EAAB78B');
        $this->addSql('ALTER TABLE catalog_item_images DROP CONSTRAINT FK_7752EA0B1DDDAF72');
        $this->addSql('ALTER TABLE catalog_items DROP CONSTRAINT FK_580D88F4CC3C66FC');
        $this->addSql('DROP TABLE catalog_characteristics');
        $this->addSql('DROP TABLE catalog_groups');
        $this->addSql('DROP TABLE catalog_item_characteristics');
        $this->addSql('DROP TABLE catalog_item_images');
        $this->addSql('DROP TABLE catalog_items');
        $this->addSql('DROP TABLE catalogs');
        $this->addSql('DROP TABLE feedback_from_map');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750');
        $this->addSql('CREATE INDEX idx_75ea56e016ba31db ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0e3bd61ce ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0fb7336f0 ON messenger_messages (queue_name)');
    }
}
