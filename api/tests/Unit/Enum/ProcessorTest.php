<?php

namespace App\Tests\Enum;

use App\Enum\Processor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Processor::class)]
final class ProcessorTest extends TestCase
{
    public function testHasMetricsEndpoint(): void
    {
        foreach (Processor::cases() as $processor) {
            if (Processor::SUMMARY === $processor) {
                self::assertFalse($processor->hasMetricsEndpoint());
                continue;
            }
            self::assertTrue($processor->hasMetricsEndpoint());
        }
    }
}
