<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241115163620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create entry table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE entry (id UUID NOT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_created_at ON entry (created_at)');
        $this->addSql('CREATE INDEX idx_content_fulltext ON entry (content)');
        $this->addSql('COMMENT ON COLUMN entry.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN entry.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN entry.updated_at IS \'(DC2Type:datetimetz_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE entry');
    }
}
