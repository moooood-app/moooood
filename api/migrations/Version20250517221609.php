<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250517221609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop IFS support';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                ALTER TABLE parts DROP CONSTRAINT fk_6940a7fea76ed395
            SQL);
        $this->addSql(<<<'SQL'
                DROP TABLE parts
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE entries DROP CONSTRAINT fk_2df8b3c54ce34bec
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX idx_2df8b3c54ce34bec
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE entries DROP part_id
            SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                CREATE TABLE parts (id UUID NOT NULL, name VARCHAR(100) NOT NULL, colors JSONB DEFAULT '[]' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(id))
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX idx_6940a7fea76ed395 ON parts (user_id)
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE parts ADD CONSTRAINT fk_6940a7fea76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE entries ADD part_id UUID DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE entries ADD CONSTRAINT fk_2df8b3c54ce34bec FOREIGN KEY (part_id) REFERENCES parts (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX idx_2df8b3c54ce34bec ON entries (part_id)
            SQL);
    }
}
