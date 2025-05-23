<?php

namespace App\Messenger\Serializer;

use App\Message\NewEntryProcessorMessage;

final class NewEntryProcessorMessageSerializer extends AbstractSerializer
{
    protected function getMessageClass(): string
    {
        return NewEntryProcessorMessage::class;
    }
}
