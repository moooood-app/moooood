<?php

namespace App\MessageHandler;

use App\Awards\AwardOrchestrator;
use App\Enum\AwardType;
use App\Message\Awards\AwardEventMessageInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AwardEventMessageHandler
{
    public function __construct(
        private AwardOrchestrator $awardOrchestrator,
    ) {
    }

    public function __invoke(AwardEventMessageInterface $message): void
    {
        $awardTypes = AwardType::getTypesForAwardEvent($message);

        $this->awardOrchestrator->checkAwards(
            $message->getUser(),
            ...$awardTypes,
        );
    }
}
