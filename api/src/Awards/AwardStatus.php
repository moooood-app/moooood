<?php

namespace App\Awards;

use App\Entity\Awards\Award;

final readonly class AwardStatus
{
    public function __construct(
        private Award $award,
        private bool $isGranted,
        private ?int $progress = null,
    ) {
    }

    public function getAward(): Award
    {
        return $this->award;
    }

    public function isGranted(): bool
    {
        return $this->isGranted;
    }

    public function getProgress(): ?int
    {
        return $this->progress;
    }
}
