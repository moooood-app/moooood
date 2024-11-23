<?php

namespace App\Tests\Unit\Repository;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(UserRepository::class)]
#[CoversClass(User::class)]
final class UserRepositoryTest extends KernelTestCase
{
    public function testUpgradePassword(): void
    {
        $container = self::getContainer();

        $repository = $container->get(UserRepository::class);

        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        $repository->upgradePassword($user, 'new_hashed_password');

        /** @var User */
        $updatedUser = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        self::assertSame('new_hashed_password', $updatedUser->getPassword());
    }
}
