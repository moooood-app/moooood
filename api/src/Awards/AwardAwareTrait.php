<?php

namespace App\Awards;

use App\Entity\Awards\Award;

trait AwardAwareTrait
{
    protected Award $award;

    public function withAward(Award $award): static
    {
        $this->award = $award;

        return clone $this;
    }
}
