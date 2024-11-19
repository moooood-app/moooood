<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Entity\Metrics\MetricsIdentifierInterface;
use App\Entity\User;
use App\Enum\Metrics\GroupingCriteria;
use App\Enum\Processor;

interface MetricsRepositoryInterface
{
    /**
     * @return array<MetricsIdentifierInterface>
     */
    public function getMetrics(User $user, GroupingCriteria $grouping, ?Processor $processor = null): array;
}
