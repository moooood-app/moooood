<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum\Metrics;

use App\Enum\Metrics\GroupingCriteria;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GroupingCriteria::class)]
final class GroupingCriteriaTest extends TestCase
{
    public function testGetSelectExpression(): void
    {
        $alias = 'test_alias';

        self::assertSame('test_alias.created_at', GroupingCriteria::ENTRY->getDateSelector($alias));
        self::assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM-DD HH24:00:00')", GroupingCriteria::HOUR->getDateSelector($alias));
        self::assertSame("DATE_TRUNC('day', test_alias.created_at)", GroupingCriteria::DAY->getDateSelector($alias));
        self::assertSame("DATE_TRUNC('week', test_alias.created_at)", GroupingCriteria::WEEK->getDateSelector($alias));
        self::assertSame("DATE_TRUNC('month', test_alias.created_at)", GroupingCriteria::MONTH->getDateSelector($alias));
    }
}
