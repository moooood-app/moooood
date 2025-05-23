<?php

declare(strict_types=1);

namespace App\Message;

use App\Enum\Processor;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'post-processing-topic')]
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
