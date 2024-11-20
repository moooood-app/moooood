<?php

declare(strict_types=1);

namespace App\Metadata\Metrics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Enum\Processor;
use App\State\Provider\Metrics\MetricsProvider;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class MetricsApiResource extends ApiResource
{
    public const EXTRA_PROPERTY_METRICS_TYPE = 'metrics_type';

    public function __construct(Processor|string $metricsType)
    {
        $type = $metricsType instanceof Processor ? $metricsType->value : $metricsType;
        parent::__construct(
            routePrefix: '/metrics',
            uriTemplate: "/{$type}",
            operations: [
                new GetCollection(
                    provider: MetricsProvider::class,
                    extraProperties: [
                        self::EXTRA_PROPERTY_METRICS_TYPE => $type,
                    ],
                ),
            ],
        );
    }
}
