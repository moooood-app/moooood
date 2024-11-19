<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Entity\User;
use App\Enum\Metrics\GroupingCriteria;

/**
 * @template T
 */
interface MetricsRepositoryInterface
{
    /**
     * @return array<T>
     */
    public function getMetrics(User $user, GroupingCriteria $grouping): array;
}
