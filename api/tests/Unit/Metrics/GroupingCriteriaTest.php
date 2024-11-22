<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum\Metrics;

use App\Enum\Metrics\GroupingCriteria;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class GroupingCriteriaTest extends TestCase
{
    public function testGetSelectExpression(): void
    {
        $alias = 'test_alias';

        self::assertSame('test_alias.id', GroupingCriteria::ENTRY->getSelectExpression($alias));
        self::assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM-DD HH24:00:00')", GroupingCriteria::HOUR->getSelectExpression($alias));
        self::assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM-DD')", GroupingCriteria::DAY->getSelectExpression($alias));
        self::assertSame("TO_CHAR(test_alias.created_at, 'IYYY-IW')", GroupingCriteria::WEEK->getSelectExpression($alias));
        self::assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM')", GroupingCriteria::MONTH->getSelectExpression($alias));
    }
}
