<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum\Metrics;

use App\Enum\Metrics\MetricsGrouping;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MetricsGrouping::class)]
final class MetricsGroupingTest extends TestCase
{
    public function testGetSelectExpression(): void
    {
        $alias = 'test_alias';

        self::assertSame('test_alias.created_at', MetricsGrouping::ENTRY->getDateSelector($alias));
        self::assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM-DD HH24:00:00')", MetricsGrouping::HOUR->getDateSelector($alias));
        self::assertSame("DATE_TRUNC('day', test_alias.created_at)", MetricsGrouping::DAY->getDateSelector($alias));
        self::assertSame("DATE_TRUNC('week', test_alias.created_at)", MetricsGrouping::WEEK->getDateSelector($alias));
        self::assertSame("DATE_TRUNC('month', test_alias.created_at)", MetricsGrouping::MONTH->getDateSelector($alias));
    }
}
