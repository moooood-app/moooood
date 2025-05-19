<?php

namespace App\Tests\Unit\MessageHandler;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Awards\AwardOrchestrator;
use App\Entity\User;
use App\Enum\AwardType;
use App\Message\Awards\NewEntryAwardMessage;
use App\MessageHandler\AwardEventMessageHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AwardEventMessageHandler::class)]
#[UsesClass(NewEntryAwardMessage::class)]
#[UsesClass(AwardType::class)]
final class AwardEventMessageHandlerTest extends TestCase
{
    public function testHandlerCallsOrchestrator(): void
    {
        $user = new User();
        $message = new NewEntryAwardMessage('/entries/123', '/users/456');

        /** @var AwardOrchestrator&MockObject $orchestrator */
        $orchestrator = $this->createMock(AwardOrchestrator::class);
        $orchestrator->expects(self::once())
            ->method('checkAwards')
            ->with($user, AwardType::ENTRIES, AwardType::STREAK)
        ;

        /** @var IriConverterInterface&MockObject */
        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->expects(self::once())
            ->method('getResourceFromIri')
            ->with($message->userIri)
            ->willReturn($user)
        ;

        $handler = new AwardEventMessageHandler($orchestrator, $iriConverter);
        $handler($message);
    }
}
