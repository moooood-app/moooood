<?php

namespace App\Tests\Unit\Enum;

use App\Entity\Entry;
use App\Entity\Part;
use App\Entity\User;
use App\Enum\AwardType;
use App\Message\Awards\AwardEventMessageInterface;
use App\Message\Awards\NewEntryEventMessage;
use App\Message\Awards\NewPartEventMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AwardType::class)]
#[UsesClass(NewEntryEventMessage::class)]
#[UsesClass(NewPartEventMessage::class)]
final class AwardTypeTest extends TestCase
{
    public function testGetTypesForAwardEvent(): void
    {
        self::assertSame(
            [AwardType::ENTRIES, AwardType::STREAK],
            AwardType::getTypesForAwardEvent(new NewEntryEventMessage((new Entry())->setUser(new User())))
        );

        self::assertSame(
            [AwardType::PARTS],
            AwardType::getTypesForAwardEvent(new NewPartEventMessage((new Part())->setUser(new User())))
        );
    }

    public function testGetTypesForAwardEventThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported message type');

        AwardType::getTypesForAwardEvent(new class implements AwardEventMessageInterface {
            public function getUser(): User
            {
                return new User();
            }
        });
    }
}
