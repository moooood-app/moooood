<?php

namespace App\DataFixtures\Awards;

use App\Awards\Checkers\EntryCountChecker;
use App\DataFixtures\UserFixtures;
use App\Entity\Awards\Award;
use App\Enum\AwardType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AwardFixtures extends Fixture
{
    /**
     * @return array<class-string>
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ([1, 10, 100] as $i => $entryCount) {
            $award = (new Award())
                ->setName("{$entryCount} entries")
                ->setDescription("Awarded for posting {$entryCount} entries")
                ->setImage('https://dummyimage.com/600x400/333/fff&text=Award')
                ->setType(AwardType::ENTRIES)
                ->setPriority(0 - $i)
                ->setCriteria([EntryCountChecker::ENTRY_COUNT_CRITERIA => $entryCount])
            ;
            $manager->persist($award);
        }

        $manager->flush();
    }
}
