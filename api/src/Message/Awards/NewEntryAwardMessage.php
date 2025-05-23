<?php

declare(strict_types=1);

namespace App\Message\Awards;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'award-events-topic')]
final readonly class NewEntryAwardMessage
{
    public function __construct(
        public readonly string $entryIri,
        public readonly string $userIri,
    ) {
    }
}
