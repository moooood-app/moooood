<?php

namespace App\Messenger\Serializer;

use App\Message\Awards\NewEntryAwardMessage;

final class NewEntryAwardMessageSerializer extends AbstractSerializer
{
    protected function getMessageClass(): string
    {
        return NewEntryAwardMessage::class;
    }
}
