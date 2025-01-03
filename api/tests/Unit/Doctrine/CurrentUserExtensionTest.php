<?php

namespace App\Tests\Unit\Doctrine;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Doctrine\CurrentUserExtension;
use App\Entity\Entry;
use App\Entity\EntryMetadata;
use App\Entity\User;
use App\EventListener\NewEntryListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(CurrentUserExtension::class)]
#[UsesClass(NewEntryListener::class)]
final class CurrentUserExtensionTest extends KernelTestCase
{
    public function testApplyToCollectionAddsCorrectWhereClause(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        /** @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $user
            ->expects(self::once())->method('getId')
            ->willReturn($uuid = new Uuid('00000000-0000-0000-0000-000000000042'))
        ;

        $security = $this->createMock(Security::class);
        $security->expects(self::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(User::class, 'u')
        ;

        $extension = new CurrentUserExtension($security);
        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            Entry::class,
        );

        $parameters = $queryBuilder->getParameters();

        self::assertStringContainsString('u.user = :current_user', (string) $queryBuilder->getDQL());

        self::assertCount(1, $parameters);
        /** @var Parameter $userParameter */
        $userParameter = $parameters->first();
        self::assertSame('current_user', $userParameter->getName());
        self::assertSame($uuid, $userParameter->getValue());
    }

    /**
     * @param class-string $resourceClass
     */
    #[DataProvider('provideResourceClasses')]
    public function testApplyToItemAddsCorrectWhereClause(string $resourceClass, string $expectedProperty): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        /** @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $user
            ->expects(self::once())->method('getId')
            ->willReturn($uuid = new Uuid('00000000-0000-0000-0000-000000000042'))
        ;

        $security = $this->createMock(Security::class);
        $security->expects(self::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(User::class, 'u')
        ;

        $extension = new CurrentUserExtension($security);
        $extension->applyToItem(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            $resourceClass,
            [],
        );

        self::assertStringContainsString("u.{$expectedProperty} = :current_user", (string) $queryBuilder->getDQL());

        $parameters = $queryBuilder->getParameters();

        /** @var Parameter $userParameter */
        $userParameter = $parameters->first();
        self::assertSame('current_user', $userParameter->getName());
        self::assertSame($uuid, $userParameter->getValue());
    }

    /**
     * @return iterable<array{class-string, string}>
     */
    public static function provideResourceClasses(): iterable
    {
        yield [User::class, 'id'];
        yield [Entry::class, 'user'];
    }

    #[DataProvider('provideSecurityUser')]
    public function testExtensionIsNotApplicable(?User $user): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $security = $this->createMock(Security::class);
        $security->expects(self::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select('u')
            ->from(EntryMetadata::class, 'u')
        ;

        $extension = new CurrentUserExtension($security);
        $extension->applyToCollection(
            $queryBuilder,
            $this->createMock(QueryNameGeneratorInterface::class),
            EntryMetadata::class,
        );

        $parameters = $queryBuilder->getParameters();

        self::assertStringNotContainsString('u.user = :current_user', (string) $queryBuilder->getDQL());

        self::assertEmpty($parameters);
    }

    /**
     * @return iterable<array{User|null}>
     */
    public static function provideSecurityUser(): iterable
    {
        yield [null];
        yield [new User()];
    }
}
