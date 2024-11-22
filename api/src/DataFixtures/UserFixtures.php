<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user
            ->setEmail('peter@forcepure.com')
            ->setFirstName('Peter')
            ->setLastName('Mac Calloway')
            ->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    'password',
                )
            )
        ;
        $manager->persist($user);

        $manager->flush();
    }
}
