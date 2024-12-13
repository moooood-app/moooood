<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213052927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image to award and timezone to user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE award ADD image VARCHAR(512) NOT NULL');
        $this->addSql('ALTER TABLE users ADD timezone VARCHAR(64) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP timezone');
        $this->addSql('ALTER TABLE award DROP image');
    }
}
