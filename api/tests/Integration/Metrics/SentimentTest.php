<?php

namespace App\Tests\Integration\Metrics;

use App\Dto\Metrics\MetricsQuery;
use App\Entity\Metrics\Sentiment;
use App\Entity\User;
use App\Enum\Metrics\GroupingCriteria;
use App\EventListener\EntryWriteListener;
use App\Notifier\EntrySnsNotifier;
use App\Repository\Metrics\SentimentRepository;
use App\Repository\UserRepository;
use App\State\Provider\Metrics\MetricsProvider;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Sentiment::class)]
#[CoversClass(SentimentRepository::class)]
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(MetricsQuery::class)]
#[CoversClass(GroupingCriteria::class)]
#[CoversClass(EntryWriteListener::class)]
#[CoversClass(EntrySnsNotifier::class)]
#[CoversClass(MetricsProvider::class)]
final class SentimentTest extends AbstractMetricsTestCase
{
    protected function getMetricsName(): string
    {
        return 'sentiment';
    }

    protected function assertResponseIsValid(array $data): void
    {
        self::assertSame(0, $data['totalItems']);
        self::assertSame([], $data['member']);
    }
}
