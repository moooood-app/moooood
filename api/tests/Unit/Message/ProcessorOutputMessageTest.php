<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Entity\Entry;
use App\Enum\Processor;
use App\Message\ProcessorOutputMessage;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ProcessorOutputMessageTest extends TestCase
{
    /**
     * @covers \App\Message\ProcessorOutputMessage::getEntry
     * @covers \App\Message\ProcessorOutputMessage::getProcessor
     * @covers \App\Message\ProcessorOutputMessage::getResult
     */
    public function testGetResult(): void
    {
        $entry = new Entry();
        $processor = Processor::SENTIMENT;

        $result = ['key' => 'value'];
        $message = new ProcessorOutputMessage($entry, $result, $processor);

        self::assertSame($entry, $message->getEntry());
        self::assertSame($result, $message->getResult());
        self::assertSame($processor, $message->getProcessor());
    }
}
