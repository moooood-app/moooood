<?php

declare(strict_types=1);

namespace Tests\Unit\App\Enum\Metrics;

use App\Enum\Metrics\GroupingCriteria;
use PHPUnit\Framework\TestCase;

class GroupingCriteriaTest extends TestCase
{
    public function testGetSelectExpression(): void
    {
        $alias = 'test_alias';

        $this->assertSame('test_alias.entry_id', GroupingCriteria::ENTRY->getSelectExpression($alias));
        $this->assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM-DD HH24:00:00')", GroupingCriteria::HOUR->getSelectExpression($alias));
        $this->assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM-DD')", GroupingCriteria::DAY->getSelectExpression($alias));
        $this->assertSame("TO_CHAR(test_alias.created_at, 'IYYY-IW')", GroupingCriteria::WEEK->getSelectExpression($alias));
        $this->assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM')", GroupingCriteria::MONTH->getSelectExpression($alias));
    }

    public function testGetDefaultDateFrom(): void
    {
        $this->assertSame('1 week ago', GroupingCriteria::ENTRY->getDefaultDateFrom());
        $this->assertSame('1 week ago', GroupingCriteria::HOUR->getDefaultDateFrom());
        $this->assertSame('1 month ago', GroupingCriteria::DAY->getDefaultDateFrom());
        $this->assertSame('3 months ago', GroupingCriteria::WEEK->getDefaultDateFrom());
        $this->assertSame('1 year ago', GroupingCriteria::MONTH->getDefaultDateFrom());
    }

    public function testCalculateDateUntil(): void
    {
        $dateFrom = new \DateTime('2024-11-01');

        $this->assertSame('2024-11-08', GroupingCriteria::ENTRY->calculateDateUntil(clone $dateFrom)->format('Y-m-d'));
        $this->assertSame('2024-11-08', GroupingCriteria::HOUR->calculateDateUntil(clone $dateFrom)->format('Y-m-d'));
        $this->assertSame('2024-12-01', GroupingCriteria::DAY->calculateDateUntil(clone $dateFrom)->format('Y-m-d'));
        $this->assertSame('2025-02-01', GroupingCriteria::WEEK->calculateDateUntil(clone $dateFrom)->format('Y-m-d'));
        $this->assertSame('2025-11-01', GroupingCriteria::MONTH->calculateDateUntil(clone $dateFrom)->format('Y-m-d'));
    }

    public function testAdjustDateFromToPeriodStart(): void
    {
        $dateFrom = new \DateTime('2024-11-19 15:30:45');

        $expectedDates = [
            GroupingCriteria::ENTRY->value => '2024-11-18 00:00:00',
            GroupingCriteria::HOUR->value => '2024-11-18 00:00:00',
            GroupingCriteria::DAY->value => '2024-11-01 00:00:00',
            GroupingCriteria::WEEK->value => '2024-11-18 00:00:00',
            GroupingCriteria::MONTH->value => '2024-11-01 00:00:00',
        ];

        foreach (GroupingCriteria::cases() as $criteria) {
            $date = clone $dateFrom;
            $criteria->adjustDateFromToPeriodStart($date);
            $this->assertSame($expectedDates[$criteria->value], $date->format('Y-m-d H:i:s'));
        }
    }
}
