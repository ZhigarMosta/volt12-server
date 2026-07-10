<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add site_settings table, seed default robots.txt';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS site_settings (
            id SERIAL PRIMARY KEY,
            code VARCHAR(64) NOT NULL,
            value TEXT DEFAULT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
        )');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_site_settings_code ON site_settings (code)');

        $robots = "User-agent: *\nDisallow: /cart\nDisallow: /checkout\nDisallow: /profile\nDisallow: /orders\nDisallow: /compare\nDisallow: /favorites";
        $this->addSql(
            'INSERT INTO site_settings (code, value, updated_at) VALUES (:code, :value, NOW()) ON CONFLICT (code) DO NOTHING',
            ['code' => 'robots_txt', 'value' => $robots],
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS site_settings');
    }
}
