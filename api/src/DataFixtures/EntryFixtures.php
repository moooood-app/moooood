<?php

namespace App\DataFixtures;

use App\DataFixtures\Helpers\Metadata\ComplexityMetadataHelper;
use App\DataFixtures\Helpers\Metadata\KeywordsMetadataHelper;
use App\DataFixtures\Helpers\Metadata\SentimentMetadataHelper;
use App\Entity\User;
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

        /** @var User */
        $user = $this->getReference(UserFixtures::FIRST_USER, User::class);

        $entryHelper = (new EntryHelper($faker))
            ->addMetadataHelper(new ComplexityMetadataHelper($faker))
            ->addMetadataHelper(new SentimentMetadataHelper($faker))
            ->addMetadataHelper(new KeywordsMetadataHelper($faker))
        ;

        for ($d = 0; $d < 7; ++$d) {
            for ($i = 1; $i <= rand(1, 5); ++$i) {
                $entry = $entryHelper->provideEntry($user);
                $entry->createdAt = new \DateTimeImmutable("-{$d} days");
                $manager->persist($entry);
            }
        }

        $manager->flush();
    }
}
