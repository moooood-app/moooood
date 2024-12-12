<?php

namespace App\Awards\Checkers;

use App\Awards\AwardAwareTrait;
use App\Awards\Contracts\ChainableAwardCheckerInterface;

abstract class AbstractChainableAwardChecker implements ChainableAwardCheckerInterface
{
    use AwardAwareTrait;
    protected ?ChainableAwardCheckerInterface $next = null;

    public function setNext(ChainableAwardCheckerInterface $next): static
    {
        $this->next = $next;

        return $this;
    }
}
