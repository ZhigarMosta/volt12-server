<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260825140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Источник заказа: user_orders.source (checkout | quick) для кнопки «Купить в один клик»';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE user_orders ADD source VARCHAR(32) DEFAULT 'checkout' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_orders DROP source');
    }
}
