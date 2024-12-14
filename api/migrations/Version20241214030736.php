<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241214030736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add on delete cascade to entries_metadata';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entries_metadata DROP CONSTRAINT FK_275E8461BA364942');
        $this->addSql('ALTER TABLE entries_metadata ADD CONSTRAINT FK_275E8461BA364942 FOREIGN KEY (entry_id) REFERENCES entries (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE entries_metadata DROP CONSTRAINT fk_275e8461ba364942');
        $this->addSql('ALTER TABLE entries_metadata ADD CONSTRAINT fk_275e8461ba364942 FOREIGN KEY (entry_id) REFERENCES entries (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
