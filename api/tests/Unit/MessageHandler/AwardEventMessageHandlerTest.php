<?php

namespace App\Tests\Unit\MessageHandler;

use App\Awards\AwardOrchestrator;
use App\Entity\Entry;
use App\Entity\User;
use App\Enum\AwardType;
use App\Message\Awards\NewEntryEventMessage;
use App\MessageHandler\AwardEventMessageHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AwardEventMessageHandler::class)]
#[UsesClass(NewEntryEventMessage::class)]
#[UsesClass(AwardType::class)]
final class AwardEventMessageHandlerTest extends TestCase
{
    public function testHandlerCallsOrchestrator(): void
    {
        $message = new NewEntryEventMessage((new Entry())->setUser($user = new User()));

        /** @var AwardOrchestrator&MockObject $orchestrator */
        $orchestrator = $this->createMock(AwardOrchestrator::class);
        $orchestrator->expects(self::once())
            ->method('checkAwards')
            ->with($user, AwardType::ENTRIES, AwardType::STREAK)
        ;

        $handler = new AwardEventMessageHandler($orchestrator);
        $handler($message);
    }
}
