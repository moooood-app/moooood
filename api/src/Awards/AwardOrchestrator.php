<?php

namespace App\Awards;

use App\Entity\User;
use App\Enum\AwardType;
use App\Repository\Awards\AwardProgressRepository;
use App\Repository\Awards\GrantedAwardRepository;

readonly class AwardOrchestrator
{
    public function __construct(
        private AwardProgressRepository $awardProgressRepository,
        private GrantedAwardRepository $grantedAwardRepository,
        private ChainableCheckerFactory $chainableCheckerFactory,
    ) {
    }

    public function checkAwards(User $user, AwardType ...$awardTypes): void
    {
        $result = new AwardStatusCollection();

        foreach ($awardTypes as $awardType) {
            $this->chainableCheckerFactory->create($user, $awardType)?->check(
                $user,
                $result
            );
        }

        foreach ($result as $status) {
            if ($status->isGranted()) {
                $this->grantedAwardRepository->grantAward($status->getAward(), $user);
                $this->awardProgressRepository->deleteAwardProgressForAward($user, $status->getAward());

                continue;
            }

            if (null !== $status->getProgress()) {
                $this->awardProgressRepository->createOrUpdateAwardProgress($user, $status->getAward(), $status->getProgress());
            }
        }
    }
}
