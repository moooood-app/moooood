<?php

namespace App\Tests\Integration\Metrics;

use App\Dto\Metrics\MetricsQuery;
use App\Entity\Metrics\Complexity;
use App\Entity\User;
use App\Enum\Metrics\GroupingCriteria;
use App\EventListener\EntryWriteListener;
use App\Notifier\EntrySnsNotifier;
use App\Repository\Metrics\ComplexityRepository;
use App\Repository\UserRepository;
use App\State\Provider\Metrics\MetricsProvider;
use App\Tests\Integration\Traits\ValidateJsonSchemaTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Complexity::class)]
#[CoversClass(ComplexityRepository::class)]
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(MetricsQuery::class)]
#[CoversClass(GroupingCriteria::class)]
#[CoversClass(EntryWriteListener::class)]
#[CoversClass(EntrySnsNotifier::class)]
#[CoversClass(MetricsProvider::class)]
final class ComplexityTest extends AbstractMetricsTestCase
{
    use ValidateJsonSchemaTrait;

    protected function getMetricsName(): string
    {
        return 'complexity';
    }

    protected function assertResponseIsValid(object $data): void
    {
        self::assertSame(7, $data->totalItems);
        self::assertCount(7, $data->member);
        self::assertJsonSchemaIsValid($data, 'metrics/complexity.json');
    }
}
