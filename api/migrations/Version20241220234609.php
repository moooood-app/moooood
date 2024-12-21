<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241220234609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created at to awards';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE awards ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER INDEX idx_11edcffca76ed395 RENAME TO IDX_F98EE622A76ED395');
        $this->addSql('ALTER INDEX idx_11edcffc3d5282cf RENAME TO IDX_F98EE6223D5282CF');
        $this->addSql('ALTER INDEX idx_2093a1933d5282cf RENAME TO IDX_AF1BE0703D5282CF');
        $this->addSql('ALTER INDEX idx_2093a193a76ed395 RENAME TO IDX_AF1BE070A76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX idx_f98ee6223d5282cf RENAME TO idx_11edcffc3d5282cf');
        $this->addSql('ALTER INDEX idx_f98ee622a76ed395 RENAME TO idx_11edcffca76ed395');
        $this->addSql('ALTER TABLE awards DROP created_at');
        $this->addSql('ALTER INDEX idx_af1be070a76ed395 RENAME TO idx_2093a193a76ed395');
        $this->addSql('ALTER INDEX idx_af1be0703d5282cf RENAME TO idx_2093a1933d5282cf');
    }
}
