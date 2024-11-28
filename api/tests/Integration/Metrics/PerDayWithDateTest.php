<?php

namespace App\Tests\Integration\Metrics;

use App\DataFixtures\EntryFixtures;
use App\Dto\Metrics\MetricsQuery;
use App\Entity\Metrics\Complexity;
use App\Entity\Metrics\Keywords;
use App\Entity\Metrics\Sentiment;
use App\Entity\Metrics\Submissions;
use App\Entity\User;
use App\Enum\Metrics\GroupingCriteria;
use App\EventListener\EntryWriteListener;
use App\EventListener\TokenCreatedListener;
use App\Notifier\EntrySnsNotifier;
use App\Repository\Metrics\ComplexityRepository;
use App\Repository\Metrics\KeywordsRepository;
use App\Repository\Metrics\SentimentRepository;
use App\Repository\Metrics\SubmissionsRepository;
use App\Repository\UserRepository;
use App\State\Provider\Metrics\MetricsProvider;
use App\Tests\Integration\Traits\ValidateJsonSchemaTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(Complexity::class)]
#[CoversClass(Keywords::class)]
#[CoversClass(Submissions::class)]
#[CoversClass(Sentiment::class)]
#[CoversClass(ComplexityRepository::class)]
#[CoversClass(KeywordsRepository::class)]
#[CoversClass(SubmissionsRepository::class)]
#[CoversClass(SentimentRepository::class)]
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(MetricsQuery::class)]
#[CoversClass(GroupingCriteria::class)]
#[CoversClass(MetricsProvider::class)]
#[UsesClass(EntryWriteListener::class)]
#[UsesClass(EntrySnsNotifier::class)]
#[UsesClass(TokenCreatedListener::class)]
final class PerDayWithDateTest extends AbstractMetricsTestCase
{
    use ValidateJsonSchemaTrait;

    protected function assertResponseIsValid(object $data): void
    {
        self::assertSame('2018-04-01T00:00:00+00:00', $data->member[0]->date);
        self::assertSame('2018-04-30T00:00:00+00:00', $data->member[29]->date);
        // 30 days in April
        self::assertSame(30, $data->totalItems);
        self::assertCount(30, $data->member);
    }

    public static function provideQueryParameters(): iterable
    {
        yield 'per day, from '.EntryFixtures::TWO_MONTHS_BATCH_DATE => [
            GroupingCriteria::DAY,
            new \DateTime(EntryFixtures::TWO_MONTHS_BATCH_DATE),
        ];
    }
}
