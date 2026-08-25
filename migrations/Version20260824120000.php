<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260824120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Блоки «товар — товар» на странице товара: catalog_item_recommendations, catalog_item_bought_together, catalog_item_also_needed';
    }

    private const TABLES = [
        'catalog_item_recommendations' => ['uniq_ci_recommendations_pair', 'idx_ci_recommendations_item'],
        'catalog_item_bought_together' => ['uniq_ci_bought_together_pair', 'idx_ci_bought_together_item'],
        'catalog_item_also_needed'     => ['uniq_ci_also_needed_pair', 'idx_ci_also_needed_item'],
    ];

    public function up(Schema $schema): void
    {
        foreach (self::TABLES as $table => [$uniq, $idx]) {
            $this->addSql("CREATE TABLE {$table} (
                id SERIAL PRIMARY KEY,
                catalog_item_id INT NOT NULL REFERENCES catalog_items (id) ON DELETE CASCADE,
                linked_item_id INT NOT NULL REFERENCES catalog_items (id) ON DELETE CASCADE,
                position INT DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL
            )");
            $this->addSql("CREATE UNIQUE INDEX {$uniq} ON {$table} (catalog_item_id, linked_item_id)");
            $this->addSql("CREATE INDEX {$idx} ON {$table} (catalog_item_id)");
        }
    }

    public function down(Schema $schema): void
    {
        foreach (array_keys(self::TABLES) as $table) {
            $this->addSql("DROP TABLE IF EXISTS {$table}");
        }
    }
}
