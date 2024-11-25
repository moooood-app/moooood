<?php

namespace App\Tests\Unit\Enum\Metrics;

use App\Dto\Metrics\MetricsQuery;
use App\Enum\Metrics\GroupingCriteria;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;

/**
 * @internal
 */
#[CoversClass(MetricsQuery::class)]
final class MetricsQueryTest extends TestCase
{
    /**
     * @param array<bool|float|int|string> $inputData
     */
    #[DataProvider('provideMetricsQueryCases')]
    public function testMetricsQuery(
        array $inputData,
        GroupingCriteria $expectedGrouping,
        string $expectedDateFrom,
        string $expectedDateUntil,
    ): void {
        $inputBag = new InputBag($inputData);

        $query = MetricsQuery::fromInputBag($inputBag);

        self::assertSame($expectedGrouping, $query->groupingCriteria);
        self::assertSame($expectedDateFrom, $query->getDateFrom()->format('Y-m-d H:i:s'));
        self::assertSame($expectedDateUntil, $query->getDateUntil()->format('Y-m-d H:i:s'));
    }

    /**
     * @return iterable<string, array<mixed>>
     */
    public static function provideMetricsQueryCases(): iterable
    {
        yield 'Default date for ENTRY with null date_from' => [
            ['grouping' => GroupingCriteria::ENTRY->value, 'from' => null],
            GroupingCriteria::ENTRY,
            (new \DateTime())->modify('last Sunday')->modify('+1 day')->format('Y-m-d 00:00:00'),
            (new \DateTime())->modify('last Sunday')->modify('+1 day')->modify('+1 week')->format('Y-m-d 00:00:00'),
        ];

        yield 'Align HOUR to week boundary at year end' => [
            ['grouping' => GroupingCriteria::HOUR->value, 'from' => '2024-12-31'],
            GroupingCriteria::HOUR,
            '2024-12-30 00:00:00',
            '2025-01-06 00:00:00',
        ];

        yield 'Leap year DAY alignment for February 29' => [
            ['grouping' => GroupingCriteria::DAY->value, 'from' => '2024-02-29'],
            GroupingCriteria::DAY,
            '2024-02-01 00:00:00',
            '2024-03-01 00:00:00',
        ];

        yield 'Align DAY to start of the month' => [
            ['grouping' => GroupingCriteria::DAY->value, 'from' => '2024-11-20'],
            GroupingCriteria::DAY,
            '2024-11-01 00:00:00',
            '2024-12-01 00:00:00',
        ];

        yield 'Align WEEK to start of quarter crossing year boundary' => [
            ['grouping' => GroupingCriteria::WEEK->value, 'from' => '2024-12-25'],
            GroupingCriteria::WEEK,
            '2024-10-01 00:00:00',
            '2025-01-01 00:00:00',
        ];

        yield 'Align WEEK to Q1 boundary' => [
            ['grouping' => GroupingCriteria::WEEK->value, 'from' => '2024-01-01'],
            GroupingCriteria::WEEK,
            '2024-01-01 00:00:00',
            '2024-04-01 00:00:00',
        ];

        yield 'Align MONTH to start of the year' => [
            ['grouping' => GroupingCriteria::MONTH->value, 'from' => '2024-11-15'],
            GroupingCriteria::MONTH,
            '2024-01-01 00:00:00',
            '2025-01-01 00:00:00',
        ];

        yield 'Align MONTH crossing year boundary' => [
            ['grouping' => GroupingCriteria::MONTH->value, 'from' => '2023-12-31'],
            GroupingCriteria::MONTH,
            '2023-01-01 00:00:00',
            '2024-01-01 00:00:00',
        ];

        yield 'Default date for DAY with null from' => [
            ['grouping' => GroupingCriteria::DAY->value, 'from' => null],
            GroupingCriteria::DAY,
            (new \DateTime('first day of this month'))->format('Y-m-d 00:00:00'),
            (new \DateTime('first day of this month'))->modify('+1 month')->format('Y-m-d 00:00:00'),
        ];

        yield 'Default date for MONTH with null from' => [
            ['grouping' => GroupingCriteria::MONTH->value, 'from' => null],
            GroupingCriteria::MONTH,
            (new \DateTime('first day of January'))->format('Y-m-d 00:00:00'),
            (new \DateTime('first day of January'))->modify('+1 year')->format('Y-m-d 00:00:00'),
        ];
    }
}
