<?php

declare(strict_types=1);

namespace App\Metadata\Metrics;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Entity\Part;
use App\Enum\Metrics\MetricsGrouping;
use App\Enum\Processor;
use App\State\Provider\Metrics\MetricsProvider;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Date;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class MetricsApiResource extends ApiResource
{
    public const EXTRA_PROPERTY_METRICS_TYPE = 'metrics_type';
    public const FROM_DATE_FILTER_KEY = 'from';
    public const GROUPING_FILTER_KEY = 'grouping';
    public const GROUP_BY_PARTS_FILTER_KEY = 'groupByParts';

    public const METRICS_NORMALIZATION_GROUP = 'metrics:read';

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
                    description: "Get {$type} metrics for the given grouping criteria",
                ),
            ],
            paginationEnabled: false,
            parameters: [
                self::GROUPING_FILTER_KEY => new QueryParameter(
                    required: true,
                    schema: array_map(static fn (MetricsGrouping $criteria) => $criteria->value, MetricsGrouping::cases()),
                    description: 'Grouping criteria',
                ),
                self::GROUP_BY_PARTS_FILTER_KEY => new QueryParameter(
                    description: 'Whether to group by parts',
                    schema: ['type' => 'boolean'],
                ),
                self::FROM_DATE_FILTER_KEY => new QueryParameter(
                    description: 'The start date for filtering results (rounded down to the first day of the chosen grouping mechanism).',
                    schema: ['type' => 'string'],
                    constraints: [
                        new All(
                            constraints: [
                                new Date([
                                    'message' => 'The date must be in the format YYYY-MM-DD.',
                                ]),
                            ],
                        ),
                    ],
                ),
            ],
            normalizationContext: [
                'groups' => [
                    self::METRICS_NORMALIZATION_GROUP,
                    Part::SERIALIZATION_GROUP_READ_COLLECTION,
                ],
            ],
            strictQueryParameterValidation: true,
        );
    }
}
