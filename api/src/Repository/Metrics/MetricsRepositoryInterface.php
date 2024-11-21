<?php

declare(strict_types=1);

namespace App\Repository\Metrics;

use App\Dto\Metrics\MetricsQuery;
use App\Entity\Metrics\MetricsIdentifierInterface;
use App\Entity\User;

interface MetricsRepositoryInterface
{
    /**
     * @return array<MetricsIdentifierInterface>
     */
    public function getMetrics(
        User $user,
        MetricsQuery $query,
    ): array;
}
