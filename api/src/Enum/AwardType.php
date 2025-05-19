<?php

namespace App\Enum;

enum AwardType: string
{
    // Awards related to the number of entries
    // Triggered by new entries
    case ENTRIES = 'entries';

    // Awards related to the progression of the sentiment metrics
    // Triggered by scheduled jobs
    case POSITIVITY_WEEKLY = 'positivity_weekly';

    // Awards related to the number of days in a row where the user has made an entry
    // Triggered by new entries
    case STREAK = 'streak';
}
