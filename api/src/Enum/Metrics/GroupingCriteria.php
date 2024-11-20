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
            self::ENTRY => "{$alias}.entry_id",
            self::HOUR => "TO_CHAR({$alias}.created_at, 'YYYY-MM-DD HH24:00:00')",
            self::DAY => "TO_CHAR({$alias}.created_at, 'YYYY-MM-DD')",
            self::WEEK => "TO_CHAR({$alias}.created_at, 'IYYY-IW')",
            self::MONTH => "TO_CHAR({$alias}.created_at, 'YYYY-MM')",
        };
    }

    public function getDefaultDateFrom(): string
    {
        return match ($this) {
            self::ENTRY, self::HOUR => '1 week ago',
            self::DAY => '1 month ago',
            self::WEEK => '3 months ago',
            self::MONTH => '1 year ago',
        };
    }

    public function calculateDateUntil(\DateTime $dateFrom): \DateTime
    {
        $dateUntil = clone $dateFrom;

        return match ($this) {
            self::ENTRY, self::HOUR => $dateUntil->modify('+1 week'),
            self::DAY => $dateUntil->modify('+1 month'),
            self::WEEK => $dateUntil->modify('+3 months'),
            self::MONTH => $dateUntil->modify('+1 year'),
        };
    }

    public function adjustDateFromToPeriodStart(\DateTime $dateFrom): void
    {
        match ($this) {
            self::ENTRY, self::HOUR => $dateFrom->modify('this week Monday'),
            self::DAY => $dateFrom->modify('first day of this month'),
            self::WEEK => $dateFrom->modify('this week Monday'),
            self::MONTH => $dateFrom->modify('first day of this month'),
        };

        $dateFrom->setTime(0, 0, 0);
    }
}
