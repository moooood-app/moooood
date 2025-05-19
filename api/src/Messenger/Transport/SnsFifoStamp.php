<?php

declare(strict_types=1);

namespace App\Messenger\Transport;

use Symfony\Component\Messenger\Stamp\NonSendableStampInterface;

final class SnsFifoStamp implements NonSendableStampInterface
{
    public function __construct(
        private readonly ?string $messageGroupId = null,
        private readonly ?string $messageDeduplicationId = null,
    ) {
    }

    public function getMessageGroupId(): ?string
    {
        return $this->messageGroupId;
    }

    public function getMessageDeduplicationId(): ?string
    {
        return $this->messageDeduplicationId;
    }
}
