<?php

namespace App\Message\Awards;

use App\Enum\AwardType;

class ProcessWeeklySentimentAwardsMessage
{
    public function getAwardType(): AwardType
    {
        return AwardType::POSITIVITY_WEEKLY;
    }
}
