<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Добавляет поля alt/title для изображения услуги (таблица services).
 */
final class Version20260628120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление полей img_alt и img_title в таблицу services (alt/title изображения услуги).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE services ADD imgalt VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE services ADD imgtitle VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE services DROP imgalt');
        $this->addSql('ALTER TABLE services DROP imgtitle');
    }
}
