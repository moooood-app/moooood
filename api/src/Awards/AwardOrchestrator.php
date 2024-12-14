<?php

namespace App\Awards;

use App\Awards\Contracts\ChainableAwardCheckerInterface;
use App\Entity\User;
use App\Enum\AwardType;
use App\Repository\Awards\AwardProgressRepository;
use App\Repository\Awards\GrantedAwardRepository;
use Psr\Log\LoggerInterface;

readonly class AwardOrchestrator
{
    public function __construct(
        private AwardProgressRepository $awardProgressRepository,
        private GrantedAwardRepository $grantedAwardRepository,
        private AwardCheckerFactory $awardCheckerFactory,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function checkAwards(User $user, AwardType ...$awardTypes): void
    {
        foreach ($awardTypes as $awardType) {
            $checker = $this->awardCheckerFactory->create($user, $awardType);

            if (null === $checker) {
                continue;
            }

            $result = $checker->check($user);
            $this->handleResult($user, $result);

            if ($checker instanceof ChainableAwardCheckerInterface) {
                while ($result->isGranted() && null !== $checker->getNext()) {
                    $checker = $checker->getNext();
                    $result = $checker->check($user);
                    $this->handleResult($user, $result);
                }
            }
        }
    }

    private function handleResult(User $user, AwardStatus $result): void
    {
        if ($result->isGranted()) {
            $this->logger?->info('Award {award} granted to user {user}', [
                'award' => $result->getAward()->getName(),
                'user' => $user->getEmail(),
            ]);
            $this->grantedAwardRepository->grantAward($result->getAward(), $user);
            $this->awardProgressRepository->deleteAwardProgressForAward($user, $result->getAward());

            return;
        }

        if (null !== $result->getProgress()) {
            $this->logger?->info('Award {award} progress for user {user} is {progress}', [
                'award' => $result->getAward()->getName(),
                'user' => $user->getEmail(),
                'progress' => $result->getProgress(),
            ]);
            $this->awardProgressRepository->createOrUpdateAwardProgress($user, $result->getAward(), $result->getProgress());

            return;
        }

        $this->logger?->info('Award {award} not granted to user {user}', [
            'award' => $result->getAward()->getName(),
            'user' => $user->getEmail(),
        ]);
    }
}
