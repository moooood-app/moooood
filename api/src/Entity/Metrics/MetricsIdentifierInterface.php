<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use App\Enum\Metrics\GroupingCriteria;

interface MetricsIdentifierInterface
{
    public function getId(): string;

    public function getGrouping(): GroupingCriteria;
}
