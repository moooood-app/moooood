<?php

declare(strict_types=1);

namespace App\Message;

use App\Entity\Entry;
use App\Enum\Processor;

final class ProcessorOutputMessage
{
    /**
     * @param array<mixed> $result
     */
    public function __construct(
        private readonly Entry $entry,
        private readonly array $result,
        private readonly Processor $processor,
    ) {
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }

    /**
     * @return array<mixed>
     */
    public function getResult(): array
    {
        return $this->result;
    }

    public function getProcessor(): Processor
    {
        return $this->processor;
    }
}
