<?php

declare(strict_types=1);

namespace App\Metadata\Metrics;

use ApiPlatform\Metadata\QueryParameter;
use App\Enum\Metrics\GroupingCriteria;
use Symfony\Component\Validator\Constraints\Choice;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class GroupingQueryParameter extends QueryParameter
{
    public const GROUPING_FILTER_KEY = 'grouping';

    public function __construct()
    {
        $groupingOptions = array_map(
            static fn (GroupingCriteria $criteria) => $criteria->value,
            GroupingCriteria::cases(),
        );

        parent::__construct(
            key: self::GROUPING_FILTER_KEY,
            required: true,
            description: \sprintf('Grouping criteria, one of %s', implode(', ', $groupingOptions)),
            constraints: [
                new Choice(['choices' => $groupingOptions]),
            ],
        );
    }
}
