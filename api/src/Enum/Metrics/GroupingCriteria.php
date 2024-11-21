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

    public function getSelectExpression(string $alias): string
    {
        return match ($this) {
            self::ENTRY => "{$alias}.id",
            self::HOUR => "TO_CHAR({$alias}.created_at, 'YYYY-MM-DD HH24:00:00')",
            self::DAY => "TO_CHAR({$alias}.created_at, 'YYYY-MM-DD')",
            self::WEEK => "TO_CHAR({$alias}.created_at, 'IYYY-IW')",
            self::MONTH => "TO_CHAR({$alias}.created_at, 'YYYY-MM')",
        };
    }
}
