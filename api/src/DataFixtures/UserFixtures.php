<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const FIRST_USER = 'user@moooood.app';
    public const HACKER_USER = 'hacker@moooood.app';
    public const PASSWORD = 'p@ssword';

    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user
            ->setEmail(self::FIRST_USER)
            ->setFirstName('First')
            ->setLastName('User')
            ->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    self::PASSWORD,
                )
            )
        ;

        $manager->persist($user);
        $this->addReference(self::FIRST_USER, $user);

        $user = new User();
        $user
            ->setEmail(self::HACKER_USER)
            ->setFirstName('Hacker')
            ->setLastName('Man')
            ->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    self::PASSWORD,
                )
            )
            ->setGoogle('google')
            ->setApple('apple')
        ;

        $manager->persist($user);
        $this->addReference(self::HACKER_USER, $user);

        $manager->flush();
    }
}
