<?php

declare(strict_types=1);

namespace App\Metadata\Metrics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Provider\Metrics\MetricsProvider;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class MetricsApiResource extends ApiResource
{
    public function __construct(string $uriTemplate)
    {
        parent::__construct(
            routePrefix: '/metrics',
            uriTemplate: $uriTemplate,
            operations: [
                new GetCollection(
                    provider: MetricsProvider::class,
                ),
            ],
        );
    }
}
