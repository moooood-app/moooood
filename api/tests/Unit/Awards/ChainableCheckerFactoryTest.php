<?php

namespace Tests\Unit\App\Awards;

use App\Awards\AwardStatusCollection;
use App\Awards\ChainableCheckerFactory;
use App\Awards\Contracts\ChainableAwardCheckerInterface;
use App\Entity\Awards\Award;
use App\Entity\User;
use App\Enum\AwardType;
use App\Repository\Awards\AwardRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(ChainableCheckerFactory::class)]
final class ChainableCheckerFactoryTest extends TestCase
{
    public function testCreateWithNoNonGrantedAwardsReturnsNull(): void
    {
        /** @var AwardRepository|MockObject $awardRepositoryMock */
        $awardRepositoryMock = $this->createMock(AwardRepository::class);
        $awardRepositoryMock
            ->method('findNonGrantedAwards')
            ->willReturn([])
        ;

        $checker = $this->getChecker();

        $checkers = [AwardType::ENTRIES->value => $checker];

        $factory = new ChainableCheckerFactory($checkers, $awardRepositoryMock);

        /** @var User|MockObject $userMock */
        $userMock = $this->createMock(User::class);
        $userMock
            ->method('getId')
            ->willReturn(new Uuid('00000000-0000-0000-0000-000000000042'))
        ;

        $result = $factory->create($userMock, AwardType::ENTRIES);

        self::assertNull($result);
    }

    public function testCreateBuildsChainAndReturnsTopChecker(): void
    {
        $award = (new Award())
            ->setType(AwardType::ENTRIES)
            ->setPriority(100)
        ;

        /** @var AwardRepository|MockObject $awardRepositoryMock */
        $awardRepositoryMock = $this->createMock(AwardRepository::class);
        $awardRepositoryMock
            ->method('findNonGrantedAwards')
            ->willReturn([$award])
        ;

        $checker = $this->getChecker();

        $checkers = [AwardType::ENTRIES->value => $checker];

        $factory = new ChainableCheckerFactory($checkers, $awardRepositoryMock);

        /** @var User|MockObject $userMock */
        $userMock = $this->createMock(User::class);
        $userMock
            ->method('getId')
            ->willReturn(new Uuid('00000000-0000-0000-0000-000000000042'))
        ;

        $result = $factory->create($userMock, AwardType::ENTRIES);

        self::assertSame($checker, $result);
    }

    public function testCreateBuildsChainAndReturnsNullIfNoMatchingChecker(): void
    {
        $award = (new Award())
            ->setType(AwardType::PARTS)
            ->setPriority(100)
        ;

        /** @var AwardRepository|MockObject $awardRepositoryMock */
        $awardRepositoryMock = $this->createMock(AwardRepository::class);
        $awardRepositoryMock
            ->method('findNonGrantedAwards')
            ->willReturn([$award])
        ;

        $checker = $this->getChecker();

        $checkers = [AwardType::ENTRIES->value => $checker];

        $factory = new ChainableCheckerFactory($checkers, $awardRepositoryMock);

        /** @var User|MockObject $userMock */
        $userMock = $this->createMock(User::class);
        $userMock
            ->method('getId')
            ->willReturn(new Uuid('00000000-0000-0000-0000-000000000042'))
        ;

        $result = $factory->create($userMock, AwardType::ENTRIES);

        self::assertNull($result);
    }

    public function testCreateThrowsExceptionForDuplicateChecker(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Checker for type entries already exists');

        $checkers = [
            $this->getChecker(),
            $this->getChecker(),
        ];

        /** @var AwardRepository|MockObject */
        $awardRepository = $this->createMock(AwardRepository::class);

        new ChainableCheckerFactory(new \ArrayIterator($checkers), $awardRepository);
    }

    private function getChecker(): ChainableAwardCheckerInterface
    {
        return new class implements ChainableAwardCheckerInterface {
            public function withAward(Award $award): static
            {
                return $this;
            }

            public static function getSupportedType(): AwardType
            {
                return AwardType::ENTRIES;
            }

            public function check(User $user, AwardStatusCollection $awardStatusCollection): void
            {
            }

            public function setNext(ChainableAwardCheckerInterface $checker): static
            {
                return $this;
            }
        };
    }
}
