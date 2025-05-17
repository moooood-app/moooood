<?php

namespace App\DataFixtures;

use App\DataFixtures\Helpers\EntryFixturesHelper;
use App\DataFixtures\Helpers\Metadata\ComplexityMetadataHelper;
use App\DataFixtures\Helpers\Metadata\EmotionsMetadataHelper;
use App\DataFixtures\Helpers\Metadata\KeywordsMetadataHelper;
use App\DataFixtures\Helpers\Metadata\SentimentMetadataHelper;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EntryFixtures extends Fixture implements DependentFixtureInterface
{
    public const TWO_MONTHS_BATCH_DATE = '2018-04-01 00:00:00';

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

        $entryHelper = (new EntryFixturesHelper($faker))
            ->addMetadataHelper(new ComplexityMetadataHelper($faker))
            ->addMetadataHelper(new SentimentMetadataHelper($faker))
            ->addMetadataHelper(new KeywordsMetadataHelper($faker))
            ->addMetadataHelper(new EmotionsMetadataHelper($faker))
        ;

        for ($d = 0; $d < 7; ++$d) {
            for ($i = 1; $i <= 6; ++$i) {
                $entry = $entryHelper->provideEntry($user);
                $entry->setCreatedAt(new \DateTimeImmutable("{$d} days ago"));
                $manager->persist($entry);
            }
        }

        $start = new \DateTime(self::TWO_MONTHS_BATCH_DATE);
        $end = (clone $start)->modify('+2 months');
        $interval = new \DateInterval('P1D');

        $period = new \DatePeriod($start, $interval, $end);

        // Number of entries per day is equal to the day number
        foreach ($period as $date) {
            for ($i = 1; $i <= (int) $date->format('d'); ++$i) {
                $entry = $entryHelper->provideEntry($user);
                $entry->setCreatedAt(\DateTimeImmutable::createFromMutable($date));
                $manager->persist($entry);
            }
        }

        $manager->flush();
    }
}
