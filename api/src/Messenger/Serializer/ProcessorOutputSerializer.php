<?php

namespace App\Messenger\Serializer;

use App\Message\ProcessorOutputMessage;

final class ProcessorOutputSerializer extends AbstractSerializer
{
    protected function getMessageClass(): string
    {
        return ProcessorOutputMessage::class;
    }
}
