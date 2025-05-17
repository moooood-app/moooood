<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

interface MetricsIdentifierInterface
{
    public function getId(): string;

    public function getDate(): \DateTimeImmutable;
}
