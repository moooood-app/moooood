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
    public function __construct(Processor|string $processor)
    {
        $uriTemplate = $processor;
        if ($processor instanceof Processor) {
            $uriTemplate = $uriTemplate->value;
        }
        parent::__construct(
            routePrefix: '/metrics',
            uriTemplate: "/$uriTemplate",
            operations: [
                new GetCollection(provider: MetricsProvider::class),
            ],
        );
    }
}
