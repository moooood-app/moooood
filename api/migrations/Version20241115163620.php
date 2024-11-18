<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241115163620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create entries table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE entries (id UUID NOT NULL, content TEXT NOT NULL, user_id UUID NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN entries.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE entries ADD CONSTRAINT FK_2DF8B3C5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2DF8B3C5A76ED395 ON entries (user_id)');
        $this->addSql('CREATE INDEX idx_entry_created_at ON entries (created_at)');
        $this->addSql('CREATE INDEX idx_entry_content_fulltext ON entries (content)');
        $this->addSql('COMMENT ON COLUMN entries.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN entries.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN entries.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE entry');
    }
}
