<?php

declare(strict_types=1);

namespace App\Tests\Unit\Message;

use App\Entity\Entry;
use App\Enum\Processor;
use App\Message\ProcessorOutputMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ProcessorOutputMessage::class)]
#[CoversClass(Entry::class)]
final class ProcessorOutputMessageTest extends TestCase
{
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
