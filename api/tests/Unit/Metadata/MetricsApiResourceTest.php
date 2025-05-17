<?php

declare(strict_types=1);

namespace App\Tests\Metadata\Metrics;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Parameters;
use ApiPlatform\Metadata\QueryParameter;
use App\Enum\Metrics\MetricsGrouping;
use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @internal
 */
#[CoversClass(MetricsApiResource::class)]
final class MetricsApiResourceTest extends TestCase
{
    public function testMetricsApiResourceWithProcessorEnum(): void
    {
        $resource = new MetricsApiResource(Processor::COMPLEXITY);

        self::assertSame('/metrics', $resource->getRoutePrefix());
        self::assertSame('/complexity', $resource->getUriTemplate());
        self::assertFalse($resource->getPaginationEnabled());

        /** @var Operations */
        $operations = $resource->getOperations();
        self::assertCount(1, iterator_to_array($operations->getIterator()));

        /** @var \Generator */
        $operationsIterator = $operations->getIterator();
        $operation = $operationsIterator->current();

        self::assertInstanceOf(GetCollection::class, $operation);
        self::assertSame(
            ['metrics_type' => 'complexity'],
            $operation->getExtraProperties()
        );

        /** @var Parameters */
        $parameters = $resource->getParameters();
        $parameters = iterator_to_array($parameters->getIterator());
        self::assertArrayHasKey('grouping', $parameters);
        self::assertArrayHasKey('from', $parameters);

        $groupingFilter = $parameters['grouping'];
        self::assertInstanceOf(QueryParameter::class, $groupingFilter);
        self::assertTrue($groupingFilter->getRequired());
        self::assertSame(
            array_map(static fn (MetricsGrouping $criteria) => $criteria->value, MetricsGrouping::cases()),
            $groupingFilter->getSchema()
        );
        self::assertSame('Grouping criteria', $groupingFilter->getDescription());

        /** @var QueryParameter */
        $fromDateFilter = $parameters['from'];
        self::assertSame('The start date for filtering results (rounded down to the first day of the chosen grouping mechanism).', $fromDateFilter->getDescription());

        /** @var array<Constraint> */
        $constraints = $fromDateFilter->getConstraints();
        self::assertCount(1, $constraints);
        /** @var All|null */
        $all = $constraints[0];
        self::assertInstanceOf(All::class, $all);
        $allConstraints = (array) $all->constraints;
        self::assertCount(1, $allConstraints);
        self::assertInstanceOf(Date::class, $allConstraints[0]);
        self::assertSame('The date must be in the format YYYY-MM-DD.', $allConstraints[0]->message);
    }

    public function testMetricsApiResourceWithStringType(): void
    {
        $resource = new MetricsApiResource('custom_type');
        self::assertSame('/metrics', $resource->getRoutePrefix());
        self::assertSame('/custom_type', $resource->getUriTemplate());

        /** @var Operations */
        $operations = $resource->getOperations();
        self::assertCount(1, iterator_to_array($operations->getIterator()));
        /** @var \Generator */
        $operationsIterator = $operations->getIterator();
        $operation = $operationsIterator->current();

        self::assertInstanceOf(GetCollection::class, $operation);
        self::assertSame(
            ['metrics_type' => 'custom_type'],
            $operation->getExtraProperties()
        );
    }

    public function testConstructorSetsExtraPropertiesCorrectly(): void
    {
        $resource = new MetricsApiResource('custom_metric');
        self::assertSame('/metrics', $resource->getRoutePrefix());
        self::assertSame('/custom_metric', $resource->getUriTemplate());

        /** @var Operations */
        $operations = $resource->getOperations();
        self::assertCount(1, iterator_to_array($operations->getIterator()));
        /** @var \Generator */
        $operationsIterator = $operations->getIterator();
        /** @var HttpOperation */
        $operation = $operationsIterator->current();

        self::assertSame(
            ['metrics_type' => 'custom_metric'],
            $operation->getExtraProperties()
        );
    }
}
