<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308132555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE feedback_from_map_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE feedback_from_map (id INT NOT NULL, user_name VARCHAR(255) NOT NULL, map VARCHAR(255) DEFAULT NULL, product_code VARCHAR(255) DEFAULT NULL, position INT DEFAULT NULL, star_count INT NOT NULL, message VARCHAR(255) NOT NULL, feedback_link VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE feedback_from_map_id_seq CASCADE');
        $this->addSql('DROP TABLE feedback_from_map');
    }
}
