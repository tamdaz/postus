<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230708115037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__conversation AS SELECT id, created_at FROM conversation');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('CREATE TABLE conversation (id BLOB NOT NULL --(DC2Type:uuid)
        , owner_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(id), CONSTRAINT FK_8A8E26E97E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO conversation (id, created_at) SELECT id, created_at FROM __temp__conversation');
        $this->addSql('DROP TABLE __temp__conversation');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A8E26E97E3C61F9 ON conversation (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__conversation AS SELECT id, created_at FROM conversation');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('CREATE TABLE conversation (id BLOB NOT NULL --(DC2Type:uuid)
        , created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , PRIMARY KEY(id))');
        $this->addSql('INSERT INTO conversation (id, created_at) SELECT id, created_at FROM __temp__conversation');
        $this->addSql('DROP TABLE __temp__conversation');
    }
}
