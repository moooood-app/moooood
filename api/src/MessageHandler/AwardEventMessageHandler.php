<?php

namespace App\MessageHandler;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Awards\AwardOrchestrator;
use App\Entity\User;
use App\Enum\AwardType;
use App\Message\Awards\NewEntryAwardMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AwardEventMessageHandler
{
    public function __construct(
        private AwardOrchestrator $awardOrchestrator,
        private IriConverterInterface $iriConverter,
    ) {
    }

    public function __invoke(NewEntryAwardMessage $message): void
    {
        /** @var User */
        $user = $this->iriConverter->getResourceFromIri($message->userIri);

        $this->awardOrchestrator->checkAwards(
            $user,
            ...[AwardType::ENTRIES, AwardType::STREAK],
        );
    }
}
