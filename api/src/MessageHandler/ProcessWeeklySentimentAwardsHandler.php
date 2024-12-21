<?php

namespace App\MessageHandler;

use App\Awards\AwardOrchestrator;
use App\Message\Awards\ProcessWeeklySentimentAwards;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessWeeklySentimentAwardsHandler
{
    public function __construct(
        private readonly AwardOrchestrator $awardOrchestrator,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(ProcessWeeklySentimentAwards $message): void
    {
        $users = $this->userRepository->findUsersWithEntriesInConsecutivePeriodsNotGrantedAwardType(
            \DateInterval::createFromDateString('1 week'),
            $message->getAwardType(),
            5,
        );

        foreach ($users as $user) {
            $this->awardOrchestrator->checkAwards($user, $message->getAwardType());
        }
    }
}
