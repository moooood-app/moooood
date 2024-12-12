<?php

namespace App\Awards\Contracts;

use App\Entity\Awards\Award;

interface AwardAwareInterface
{
    public function withAward(Award $award): static;
}
