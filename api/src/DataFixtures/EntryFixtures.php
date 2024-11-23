<?php

namespace App\DataFixtures;

use App\Entity\Entry;
use App\Entity\EntryMetadata;
use App\Entity\User;
use App\Enum\Processor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EntryFixtures extends Fixture implements DependentFixtureInterface
{
    public const BASE_ENTRY = 'base-entry';

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $baseEntry = new Entry();
        /** @var User */
        $user = $this->getReference(UserFixtures::FIRST_USER, User::class);
        $baseEntry
            ->setUser($user)
            ->setContent($faker->paragraph)
        ;

        // 1-5 entries per day in the last week
        for ($d = 0; $d < 7; ++$d) {
            for ($i = 1; $i <= rand(1, 5); ++$i) {
                $entry = clone $baseEntry;
                $entry->createdAt = new \DateTimeImmutable("-{$d} days");
                $manager->persist($entry);

                $complexityMetadata = (new EntryMetadata())
                    ->setEntry($entry)
                    ->setProcessor(Processor::COMPLEXITY)
                    ->setMetadata([
                        'complexity_rating' => $faker->randomFloat(2, 0, 100), // Computed average complexity rating
                        'smog_index' => $faker->randomFloat(2, 6, 20), // Typically ranges from 6 to 20+
                        'gunning_fog_index' => $faker->randomFloat(2, 6, 20), // Typically ranges from 6 to 20+
                        'coleman_liau_index' => $faker->randomFloat(2, -3, 12), // Typically ranges from -3 to 12
                        'flesch_reading_ease' => $faker->randomFloat(2, 0, 100), // 0 (most difficult) to 100 (easiest)
                        'linsear_write_formula' => $faker->randomFloat(2, 0, 20), // Typically ranges from 0 to 20+
                        'readability_consensus' => $faker->randomFloat(2, 0, 100), // Typically ranges from 0 to 100
                        'flesch_kincaid_grade_level' => $faker->randomFloat(2, 0, 12), // Grade levels, e.g., 0 to 12
                        'automated_readability_index' => $faker->randomFloat(2, 0, 12), // Grade levels, e.g., 0 to 12
                        'dale_chall_readability_score' => $faker->randomFloat(2, 4, 16), // Typically 4 to 16 for grade levels
                    ])
                ;

                $manager->persist($complexityMetadata);

                $sentimentMetadata = (new EntryMetadata())
                    ->setEntry($entry)
                    ->setProcessor(Processor::SENTIMENT)
                    ->setMetadata([
                        'compound' => $faker->randomFloat(16, -1, 1),
                        'negative' => $faker->randomFloat(16, 0, 1),
                        'positive' => $faker->randomFloat(16, 0, 1),
                        'neutral' => $faker->randomFloat(16, 0, 1),
                    ])
                ;

                $manager->persist($sentimentMetadata);

                $keywords = [];
                for ($j = 1; $j <= 5; ++$j) {
                    $keywords[] = [
                        'keyword' => $faker->words(2, true),
                        'score' => $faker->randomFloat(4, 0, 1),
                    ];
                }

                for ($k = 1; $k <= 10; ++$k) {
                    $keywords[] = [
                        'keyword' => $faker->word(),
                        'score' => $faker->randomFloat(4, 0, 1),
                    ];
                }

                $keywordsMetadata = (new EntryMetadata())
                    ->setEntry($entry)
                    ->setProcessor(Processor::KEYWORDS)
                    ->setMetadata($keywords)
                ;

                $manager->persist($keywordsMetadata);
            }
        }

        $manager->flush();
    }
}
