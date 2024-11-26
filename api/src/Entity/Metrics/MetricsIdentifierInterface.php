<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use App\Entity\Part;

interface MetricsIdentifierInterface
{
    public function getId(): string;

    public function getDate(): \DateTimeImmutable;

    public function getPart(): ?Part;
}
