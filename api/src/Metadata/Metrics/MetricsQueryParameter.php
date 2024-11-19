<?php

declare(strict_types=1);

namespace App\Metadata\Metrics;

use ApiPlatform\Metadata\QueryParameter;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class MetricsQueryParameter extends QueryParameter
{
    public function __construct()
    {
        parent::__construct(key: 'grouping', required: true);
    }
}
