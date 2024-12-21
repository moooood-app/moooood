<?php

namespace App\Message\Awards;

use App\Enum\AwardType;

class ProcessWeeklySentimentAwards
{
    public function getAwardType(): AwardType
    {
        return AwardType::POSITIVITY_WEEKLY;
    }
}
