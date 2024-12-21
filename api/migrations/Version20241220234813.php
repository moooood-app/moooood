<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Enum\AwardType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241220234813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert entries and sentiment awards';
    }

    public function up(Schema $schema): void
    {
        $entries = [
            [
                'name' => 'First entry',
                'description' => 'You posted your first entry!',
                'criteria' => '{"entry_count": 1}',
                'type' => AwardType::ENTRIES->value,
                'priority' => 1,
                'image' => "''",
            ],
        ];

        $priority = 2;
        foreach ([10, 50, 100, 500, 1000] as $entryCount) {
            $entries[] = [
                'name' => "{$entryCount} entries",
                'description' => "You posted {$entryCount} entries!",
                'criteria' => "{\"entry_count\": {$entryCount}}",
                'type' => AwardType::ENTRIES->value,
                'priority' => $priority++,
                'image' => "''",
            ];
        }

        $priority = 1;
        foreach ([10, 20, 30, 40, 50] as $entryCount) {
            $entries[] = [
                'name' => "Positivity +{$entryCount}%",
                'description' => "Your positivity improved by {$entryCount}% last week",
                'criteria' => "{\"improvement_percentage\": {$entryCount}}",
                'type' => AwardType::POSITIVITY_WEEKLY->value,
                'priority' => $priority++,
                'image' => "''",
            ];
        }

        $values = [];
        foreach ($entries as $entry) {
            $values[] = \sprintf(
                "(gen_random_uuid(), '%s', '%s', '%s', '%s', %d, %s, NOW())",
                $entry['name'],
                $entry['description'],
                $entry['criteria'],
                $entry['type'],
                $entry['priority'],
                $entry['image'],
            );
        }

        $this->addSql('INSERT INTO awards (id, name, description, criteria, type, priority, image, created_at) VALUES '.implode(', ', $values));
    }

    public function down(Schema $schema): void
    {
    }
}
