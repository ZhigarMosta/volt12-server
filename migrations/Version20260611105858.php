<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260611105858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_orders and user_order_items tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE user_orders (
                id SERIAL PRIMARY KEY,
                user_id INTEGER DEFAULT NULL REFERENCES users(id) ON DELETE SET NULL,
                status VARCHAR(32) NOT NULL DEFAULT \'new\',
                first_name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                email VARCHAR(255) NOT NULL,
                street VARCHAR(255) DEFAULT NULL,
                house VARCHAR(50) DEFAULT NULL,
                entrance VARCHAR(50) DEFAULT NULL,
                apartment VARCHAR(50) DEFAULT NULL,
                city VARCHAR(255) NOT NULL,
                region VARCHAR(255) NOT NULL,
                postal_code VARCHAR(20) NOT NULL,
                comment TEXT DEFAULT NULL,
                total_price INTEGER NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMP DEFAULT NULL
            )
        ');

        $this->addSql('
            CREATE TABLE user_order_items (
                id SERIAL PRIMARY KEY,
                order_id INTEGER NOT NULL REFERENCES user_orders(id) ON DELETE CASCADE,
                catalog_item_id INTEGER DEFAULT NULL REFERENCES catalog_items(id) ON DELETE SET NULL,
                name VARCHAR(255) NOT NULL,
                price INTEGER NOT NULL DEFAULT 0,
                quantity INTEGER NOT NULL DEFAULT 1,
                total_price INTEGER NOT NULL DEFAULT 0
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_order_items');
        $this->addSql('DROP TABLE user_orders');
    }
}
