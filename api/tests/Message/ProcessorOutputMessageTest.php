<?php

declare(strict_types=1);

namespace App\Tests\Message;

use App\Entity\Entry;
use App\Enum\Processor;
use App\Message\ProcessorOutputMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProcessorOutputMessageTest extends TestCase
{
    /**
     * @covers \App\Message\ProcessorOutputMessage::getEntry
     * @covers \App\Message\ProcessorOutputMessage::getResult
     * @covers \App\Message\ProcessorOutputMessage::getProcessor
     */
    public function testGetResult(): void
    {
        $entry = new Entry();
        $processor = Processor::SENTIMENT;

        $result = ['key' => 'value'];
        $message = new ProcessorOutputMessage($entry, $result, $processor);

        $this->assertSame($entry, $message->getEntry());
        $this->assertSame($result, $message->getResult());
        $this->assertSame($processor, $message->getProcessor());
    }

}
