<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260624142151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entity_history ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE entity_history ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE service_groups DROP img_link');
        $this->addSql('ALTER TABLE user_order_items ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE user_order_items ALTER price DROP DEFAULT');
        $this->addSql('ALTER TABLE user_order_items ALTER quantity DROP DEFAULT');
        $this->addSql('ALTER TABLE user_order_items ALTER total_price DROP DEFAULT');
        $this->addSql('ALTER TABLE user_orders ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE user_orders ALTER status DROP DEFAULT');
        $this->addSql('ALTER TABLE user_orders ALTER total_price DROP DEFAULT');
        $this->addSql('ALTER TABLE user_orders ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE user_tokens ALTER type DROP DEFAULT');
        $this->addSql('ALTER TABLE users DROP pending_email');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_groups ADD img_link VARCHAR(2048) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD pending_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_tokens ALTER type SET DEFAULT \'auth\'');
        $this->addSql('CREATE SEQUENCE user_orders_id_seq');
        $this->addSql('SELECT setval(\'user_orders_id_seq\', (SELECT MAX(id) FROM user_orders))');
        $this->addSql('ALTER TABLE user_orders ALTER id SET DEFAULT nextval(\'user_orders_id_seq\')');
        $this->addSql('ALTER TABLE user_orders ALTER status SET DEFAULT \'new\'');
        $this->addSql('ALTER TABLE user_orders ALTER total_price SET DEFAULT 0');
        $this->addSql('ALTER TABLE user_orders ALTER created_at SET DEFAULT \'now()\'');
        $this->addSql('CREATE SEQUENCE user_order_items_id_seq');
        $this->addSql('SELECT setval(\'user_order_items_id_seq\', (SELECT MAX(id) FROM user_order_items))');
        $this->addSql('ALTER TABLE user_order_items ALTER id SET DEFAULT nextval(\'user_order_items_id_seq\')');
        $this->addSql('ALTER TABLE user_order_items ALTER price SET DEFAULT 0');
        $this->addSql('ALTER TABLE user_order_items ALTER quantity SET DEFAULT 1');
        $this->addSql('ALTER TABLE user_order_items ALTER total_price SET DEFAULT 0');
        $this->addSql('CREATE SEQUENCE entity_history_id_seq');
        $this->addSql('SELECT setval(\'entity_history_id_seq\', (SELECT MAX(id) FROM entity_history))');
        $this->addSql('ALTER TABLE entity_history ALTER id SET DEFAULT nextval(\'entity_history_id_seq\')');
        $this->addSql('ALTER TABLE entity_history ALTER created_at SET DEFAULT \'now()\'');
    }
}
