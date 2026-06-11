<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260608163422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_tokens (id INT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CF080AB35F37A13B ON user_tokens (token)');
        $this->addSql('CREATE INDEX IDX_CF080AB3A76ED395 ON user_tokens (user_id)');
        $this->addSql('ALTER TABLE user_tokens ADD CONSTRAINT FK_CF080AB3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE catalog_characteristics ALTER product_code DROP DEFAULT');
        $this->addSql('ALTER TABLE catalog_characteristics ALTER product_code SET NOT NULL');
        $this->addSql('ALTER TABLE catalog_items ALTER count DROP DEFAULT');
        $this->addSql('ALTER INDEX idx_e46960f1a76ed395 RENAME TO IDX_E46960F5A76ED395');
        $this->addSql('ALTER INDEX idx_e46960f11dddaf72 RENAME TO IDX_E46960F51DDDAF72');
        $this->addSql('DROP INDEX uniq_1483a5e99315f04e');
        $this->addSql('ALTER TABLE users DROP auth_token');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE user_tokens_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_tokens DROP CONSTRAINT FK_CF080AB3A76ED395');
        $this->addSql('DROP TABLE user_tokens');
        $this->addSql('ALTER TABLE catalog_characteristics ALTER product_code SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE catalog_characteristics ALTER product_code DROP NOT NULL');
        $this->addSql('ALTER TABLE users ADD auth_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_1483a5e99315f04e ON users (auth_token)');
        $this->addSql('ALTER INDEX idx_e46960f51dddaf72 RENAME TO idx_e46960f11dddaf72');
        $this->addSql('ALTER INDEX idx_e46960f5a76ed395 RENAME TO idx_e46960f1a76ed395');
        $this->addSql('ALTER TABLE catalog_items ALTER count SET DEFAULT 0');
    }
}
