<?php

namespace App\Message\Awards;

use App\Repository\EntryRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(NewEntryAwardMessage::class)]
#[UsesClass(EntryRepository::class)]
#[UsesClass(UserRepository::class)]
final class NewEntryEventMessageTest extends KernelTestCase
{
    public function testNewInstance(): void
    {
        $entryIri = '/entries/123';
        $userIri = '/users/456';
        $message = new NewEntryAwardMessage($entryIri, $userIri);
        self::assertSame($entryIri, $message->entryIri);
        self::assertSame($userIri, $message->userIri);
    }
}
