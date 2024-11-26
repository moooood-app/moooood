<?php

declare(strict_types=1);

namespace App\Enum\Metrics;

enum GroupingCriteria: string
{
    case ENTRY = 'entry';
    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';

    public function getDateSelector(string $alias): string
    {
        return match ($this) {
            self::ENTRY => "{$alias}.id",
            self::HOUR => "TO_CHAR({$alias}.created_at, 'YYYY-MM-DD HH24:00:00')",
            self::DAY => "DATE_TRUNC('day', {$alias}.created_at)",
            self::WEEK => "DATE_TRUNC('week', {$alias}.created_at + INTERVAL '1 day')",
            self::MONTH => "DATE_TRUNC('month', {$alias}.created_at)",
        };
    }
}
