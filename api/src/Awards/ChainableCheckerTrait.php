<?php

namespace App\Awards;

use App\Awards\Contracts\ChainableAwardCheckerInterface;

trait ChainableCheckerTrait
{
    protected ?ChainableAwardCheckerInterface $next = null;

    public function setNext(ChainableAwardCheckerInterface $next): static
    {
        $this->next = $next;

        return $this;
    }

    public function getNext(): ?ChainableAwardCheckerInterface
    {
        return $this->next;
    }
}
