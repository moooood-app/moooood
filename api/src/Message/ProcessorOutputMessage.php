<?php

declare(strict_types=1);

namespace App\Message;

use App\Enum\Processor;

final class ProcessorOutputMessage
{
    /**
     * @param array<mixed> $result
     */
    public function __construct(
        public readonly string $entryIri,
        public readonly array $result,
        public readonly Processor $processor,
    ) {
    }
}
