<?php

declare(strict_types=1);

namespace App\Metadata\Metrics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Enum\Metrics\GroupingCriteria;
use App\Enum\Processor;
use App\State\Provider\Metrics\MetricsProvider;
use Symfony\Component\Validator\Constraints\Date;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class MetricsApiResource extends ApiResource
{
    public const EXTRA_PROPERTY_METRICS_TYPE = 'metrics_type';
    public const FROM_DATE_FILTER_KEY = 'from';
    public const GROUPING_FILTER_KEY = 'grouping';

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
                    paginationEnabled: false,
                ),
            ],
            paginationEnabled: false,
            parameters: [
                self::GROUPING_FILTER_KEY => new QueryParameter(
                    required: true,
                    schema: array_map(static fn (GroupingCriteria $criteria) => $criteria->value, GroupingCriteria::cases()),
                    description: 'Grouping criteria',
                ),
                self::FROM_DATE_FILTER_KEY => new QueryParameter(
                    description: 'The start date for filtering results (rounded down to the first day of the chosen grouping mechanism).',
                    required: true,
                    constraints: [
                        new Date(['message' => 'The date is not valid']),
                    ],
                ),
            ]
        );
    }
}
