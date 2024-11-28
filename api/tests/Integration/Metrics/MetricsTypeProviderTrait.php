<?php

namespace App\Tests\Integration\Metrics;

use App\Enum\Processor;

trait MetricsTypeProviderTrait
{
    /**
     * @return iterable<array{string}>
     */
    public static function provideMetricsType(): iterable
    {
        foreach (Processor::cases() as $metricsType) {
            if (!$metricsType->hasMetricsEndpoint()) {
                continue;
            }
            yield $metricsType->value => [$metricsType->value];
        }

        yield 'submissions' => ['submissions'];
    }
}
