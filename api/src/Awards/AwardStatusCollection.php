<?php

namespace App\Awards;

/**
 * @implements \IteratorAggregate<AwardStatus>
 */
final class AwardStatusCollection implements \IteratorAggregate
{
    /** @var array<AwardStatus> */
    private array $statuses;

    public function __construct()
    {
        $this->statuses = [];
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->statuses);
    }

    public function add(AwardStatus $status): void
    {
        $this->statuses[] = $status;
    }
}
