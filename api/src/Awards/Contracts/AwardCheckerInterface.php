<?php

namespace App\Awards\Contracts;

use App\Awards\AwardStatusCollection;
use App\Entity\User;

interface AwardCheckerInterface extends AwardAwareInterface
{
    public function check(User $user, AwardStatusCollection $awardStatusCollection): void;
}
