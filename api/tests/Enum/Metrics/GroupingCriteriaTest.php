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

        $this->assertSame('test_alias.id', GroupingCriteria::ENTRY->getSelectExpression($alias));
        $this->assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM-DD HH24:00:00')", GroupingCriteria::HOUR->getSelectExpression($alias));
        $this->assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM-DD')", GroupingCriteria::DAY->getSelectExpression($alias));
        $this->assertSame("TO_CHAR(test_alias.created_at, 'IYYY-IW')", GroupingCriteria::WEEK->getSelectExpression($alias));
        $this->assertSame("TO_CHAR(test_alias.created_at, 'YYYY-MM')", GroupingCriteria::MONTH->getSelectExpression($alias));
    }
}
