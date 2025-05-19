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
        $entryIri = '/entries/123';
        $processor = Processor::SENTIMENT;

        $result = ['key' => 'value'];
        $message = new ProcessorOutputMessage($entryIri, $result, $processor);

        self::assertSame($entryIri, $message->entryIri);
        self::assertSame($result, $message->result);
        self::assertSame($processor, $message->processor);
    }
}
