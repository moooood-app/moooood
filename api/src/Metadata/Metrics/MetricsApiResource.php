<?php

declare(strict_types=1);

namespace App\Metadata\Metrics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Enum\Processor;
use App\State\Provider\Metrics\MetricsProvider;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class MetricsApiResource extends ApiResource
{
    public const EXTRA_PROPERTY_METRICS_PROCESSOR = 'metrics_processor';

    public function __construct(protected readonly Processor $metricsProcessor)
    {
        parent::__construct(
            routePrefix: '/metrics',
            uriTemplate: "/$metricsProcessor->value",
            operations: [
                new GetCollection(
                    provider: MetricsProvider::class,
                    extraProperties: [
                        self::EXTRA_PROPERTY_METRICS_PROCESSOR => $metricsProcessor,
                    ],
                ),
            ],
        );
    }

    public function getMetricsProcessor(): Processor
    {
        return $this->metricsProcessor;
    }
}
