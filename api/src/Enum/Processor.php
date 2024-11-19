<?php

declare(strict_types=1);

namespace App\Enum;

enum Processor: string
{
    case SENTIMENT = 'sentiment';
    case KEYWORDS = 'keywords';
    case COMPLEXITY = 'complexity';
    case SUMMARY = 'summary';
}
