<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250518000430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create unique index on users email';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)
            SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
                DROP INDEX UNIQ_1483A5E9E7927C74
            SQL);
    }
}
