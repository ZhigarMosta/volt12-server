<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize users.email to lowercase';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE users SET email = LOWER(email) WHERE email <> LOWER(email)');
    }

    public function down(Schema $schema): void
    {
        // Регистр исходных данных не восстановить — нормализация необратима.
    }
}
