<?php

namespace App\DataFixtures;

use App\DataFixtures\Helpers\Metadata\ComplexityMetadataHelper;
use App\DataFixtures\Helpers\Metadata\KeywordsMetadataHelper;
use App\DataFixtures\Helpers\Metadata\SentimentMetadataHelper;
use App\Entity\Part;
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
            PartFixtures::class,
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
            for ($i = 1; $i <= rand(1, 6); ++$i) {
                $entry = $entryHelper->provideEntry($user);
                $entry->setCreatedAt(new \DateTimeImmutable("-{$d} days"));
                $part = null;
                if (6 !== $i) {
                    $part = $this->getReference("part-{$i}", Part::class);
                }
                $entry->setPart($part);
                $manager->persist($entry);
            }
        }

        $manager->flush();
    }
}
