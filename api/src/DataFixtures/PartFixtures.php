<?php

namespace App\DataFixtures;

use App\Entity\Part;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PartFixtures extends Fixture implements DependentFixtureInterface
{
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
        $user = $this->getReference(UserFixtures::FIRST_USER);
        for ($i = 1; $i <= 5; ++$i) {
            $part = (new Part())
                ->setUser($user)
                ->setName($faker->word)
                ->setColors(self::generateRandomColors())
            ;
            $this->addReference("part-{$i}", $part);
            $manager->persist($part);
        }

        $manager->flush();
    }

    /**
     * @return array<string>
     */
    private static function generateRandomColors(): array
    {
        return array_map(
            static fn () => \sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
            range(1, 5),
        );
    }
}
