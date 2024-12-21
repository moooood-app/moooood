<?php

namespace App\Enum;

use App\Message\Awards\AwardEventMessageInterface;
use App\Message\Awards\NewEntryEventMessage;
use App\Message\Awards\NewPartEventMessage;

enum AwardType: string
{
    // Awards related to the number of entries
    // Triggered by new entries
    case ENTRIES = 'entries';

    // Awards related to the number of parts the user has created and posted entries for
    // Triggered by new parts
    case PARTS = 'parts';

    // Awards related to the progression of the sentiment metrics
    // Triggered by scheduled jobs
    case POSITIVITY_WEEKLY = 'positivity_weekly';

    // Awards related to the number of days in a row where the user has made an entry
    // Triggered by new entries
    case STREAK = 'streak';

    /**
     * @return array<AwardType>
     */
    public static function getTypesForAwardEvent(AwardEventMessageInterface $message): array
    {
        return match ($message::class) {
            NewEntryEventMessage::class => [self::ENTRIES, self::STREAK],
            NewPartEventMessage::class => [self::PARTS],
            default => throw new \InvalidArgumentException('Unsupported message type'),
        };
    }
}
