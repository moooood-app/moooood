<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const FIRST_USER = 'user@moooood.app';

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
                    'password',
                )
            )
        ;
        $manager->persist($user);
        $this->addReference($user->getEmail(), $user);

        $manager->flush();
    }
}
