<?php

namespace App\Awards\Contracts;

interface ChainableAwardCheckerInterface extends AwardCheckerInterface, AwardAwareInterface
{
    public function setNext(self $checker): static;

    public function getNext(): ?self;
}
