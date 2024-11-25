<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241125040729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create parts table and add relation to entries';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE parts (id UUID NOT NULL, name VARCHAR(100) NOT NULL, colors JSONB DEFAULT \'[]\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_490F70C6A76ED395 ON parts (user_id)');
        $this->addSql('ALTER TABLE parts ADD CONSTRAINT FK_490F70C6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE entries ADD part_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE entries ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE entries ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE entries ADD CONSTRAINT FK_2DF8B3C54CE34BEC FOREIGN KEY (part_id) REFERENCES parts (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2DF8B3C54CE34BEC ON entries (part_id)');
        $this->addSql('ALTER TABLE entries_metadata ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER INDEX uniq_29c18cc5c74f2195 RENAME TO UNIQ_F02938B8C74F2195');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE parts DROP CONSTRAINT FK_490F70C6A76ED395');
        $this->addSql('DROP TABLE parts');
        $this->addSql('ALTER INDEX uniq_f02938b8c74f2195 RENAME TO uniq_29c18cc5c74f2195');
        $this->addSql('ALTER TABLE entries DROP CONSTRAINT FK_2DF8B3C54CE34BEC');
        $this->addSql('DROP INDEX IDX_2DF8B3C54CE34BEC');
        $this->addSql('ALTER TABLE entries DROP part_id');
        $this->addSql('ALTER TABLE entries ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE entries ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE entries_metadata ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
    }
}
