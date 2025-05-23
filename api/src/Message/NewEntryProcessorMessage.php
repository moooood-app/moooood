<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'new-entry-topic')]
final readonly class NewEntryProcessorMessage
{
    public function __construct(
        public readonly string $entryIri,
        public readonly string $content,
    ) {
    }
}
