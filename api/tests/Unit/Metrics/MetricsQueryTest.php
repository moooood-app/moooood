<?php

namespace App\Tests\Unit\Enum\Metrics;

use App\Enum\Metrics\MetricsGrouping;
use App\Repository\Metrics\MetricsQuery;
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
        MetricsGrouping $expectedGrouping,
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
            ['grouping' => MetricsGrouping::ENTRY->value, 'from' => null],
            MetricsGrouping::ENTRY,
            (new \DateTime())->format('Y-m-d 00:00:00'),
            (new \DateTime())->modify('+1 week')->format('Y-m-d 00:00:00'),
        ];

        yield 'Align HOUR to week boundary at year end' => [
            ['grouping' => MetricsGrouping::HOUR->value, 'from' => '2024-12-31'],
            MetricsGrouping::HOUR,
            '2024-12-31 00:00:00',
            '2025-01-07 00:00:00',
        ];

        yield 'DAY is unchanged' => [
            ['grouping' => MetricsGrouping::DAY->value, 'from' => '2024-11-20'],
            MetricsGrouping::DAY,
            '2024-11-20 00:00:00',
            '2024-12-20 00:00:00',
        ];

        yield 'Align WEEK to cross year boundary' => [
            ['grouping' => MetricsGrouping::WEEK->value, 'from' => '2024-12-25'],
            MetricsGrouping::WEEK,
            '2024-12-23 00:00:00',
            '2025-01-20 00:00:00',
        ];

        yield 'Align WEEK to first day of year' => [
            ['grouping' => MetricsGrouping::WEEK->value, 'from' => '2025-01-01'],
            MetricsGrouping::WEEK,
            '2024-12-30 00:00:00',
            '2025-01-27 00:00:00',
        ];

        yield 'Align MONTH to start of the year' => [
            ['grouping' => MetricsGrouping::MONTH->value, 'from' => '2024-11-15'],
            MetricsGrouping::MONTH,
            '2024-01-01 00:00:00',
            '2025-01-01 00:00:00',
        ];

        yield 'Align MONTH crossing year boundary' => [
            ['grouping' => MetricsGrouping::MONTH->value, 'from' => '2023-12-31'],
            MetricsGrouping::MONTH,
            '2023-01-01 00:00:00',
            '2024-01-01 00:00:00',
        ];

        yield 'Default date for DAY with null from' => [
            ['grouping' => MetricsGrouping::DAY->value, 'from' => null],
            MetricsGrouping::DAY,
            (new \DateTime())->format('Y-m-d 00:00:00'),
            (new \DateTime())->modify('+1 month')->format('Y-m-d 00:00:00'),
        ];

        yield 'Default date for MONTH with null from' => [
            ['grouping' => MetricsGrouping::MONTH->value, 'from' => null],
            MetricsGrouping::MONTH,
            (new \DateTime('first day of January'))->format('Y-m-d 00:00:00'),
            (new \DateTime('first day of January'))->modify('+1 year')->format('Y-m-d 00:00:00'),
        ];
    }
}
