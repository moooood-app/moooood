<?php

namespace App\Metadata\Metrics;

use ApiPlatform\Metadata\QueryParameter;
use App\Enum\Metrics\GroupingCriteria;
use Symfony\Component\Validator\Constraints\Date;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class FromDateQueryParameter extends QueryParameter
{
    public const FROM_DATE_FILTER_KEY = 'from';

    public function __construct()
    {
        $defaults = implode(', ', array_map(
            static fn (GroupingCriteria $criteria): string => \sprintf(
                '%s: %s',
                $criteria->value,
                $criteria->getDefaultDateFrom(),
            ),
            GroupingCriteria::cases(),
        ));

        parent::__construct(
            key: self::FROM_DATE_FILTER_KEY,
            description: "The start date for filtering results (rounded down to the first day of the chosen grouping mechanism). Defaults: {$defaults}. The period of analysis will follow the same date range as the default from period.",
            required: true,
            constraints: [
                new Date(['message' => 'The date is not valid']),
            ],
        );
    }
}
