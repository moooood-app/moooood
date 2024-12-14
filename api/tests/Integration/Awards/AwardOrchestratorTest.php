<?php

namespace App\Tests\Integration\Awards;

use App\Awards\AwardCheckerFactory;
use App\Awards\AwardOrchestrator;
use App\Awards\AwardStatus;
use App\Awards\AwardStatusCollection;
use App\Awards\Checkers\EntryCountChecker;
use App\DataFixtures\UserFixtures;
use App\Entity\Awards\GrantedAward;
use App\Entity\Entry;
use App\Entity\User;
use App\Enum\AwardType;
use App\Repository\Awards\AwardProgressRepository;
use App\Repository\Awards\AwardRepository;
use App\Repository\Awards\GrantedAwardRepository;
use App\Repository\EntryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(AwardOrchestrator::class)]
#[CoversClass(AwardStatus::class)]
#[CoversClass(AwardStatusCollection::class)]
#[CoversClass(AwardCheckerFactory::class)]
#[CoversClass(EntryCountChecker::class)]
#[CoversClass(AwardProgressRepository::class)]
#[CoversClass(AwardRepository::class)]
#[CoversClass(GrantedAwardRepository::class)]
#[CoversClass(EntryRepository::class)]
#[CoversClass(UserRepository::class)]
final class AwardOrchestratorTest extends KernelTestCase
{
    public function testAwardOrchestratorGrantAwardsAndUpdateProgress(): void
    {
        self::bootKernel();

        /** @var UserRepository */
        $userRepository = self::getContainer()->get(UserRepository::class);
        /** @var User */
        $user = $userRepository->findOneBy(['email' => UserFixtures::USER_NO_DATA]);

        $entry = (new Entry())
            ->setUser($user)
            ->setContent('test')
        ;

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($entry);
        $entityManager->flush();

        /** @var AwardOrchestrator */
        $orchestrator = self::getContainer()->get(AwardOrchestrator::class);
        $orchestrator->checkAwards($user, AwardType::ENTRIES);

        /** @var GrantedAwardRepository */
        $grantedAwardRepository = self::getContainer()->get(GrantedAwardRepository::class);
        $grantedAwards = $grantedAwardRepository->findBy(['user' => $user]);

        self::assertCount(1, $grantedAwards);

        /** @var GrantedAward */
        $grantedAward = $grantedAwards[0];

        self::assertSame($user, $grantedAward->getUser());
        self::assertSame(AwardType::ENTRIES, $grantedAward->getAward()->getType());
        self::assertSame(['entry_count' => 1], $grantedAward->getAward()->getCriteria());
    }
}
