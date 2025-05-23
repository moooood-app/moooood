<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Entry;
use App\Entity\User;
use App\EventListener\NewEntryListener;
use App\Message\Awards\NewEntryAwardMessage;
use App\Message\NewEntryProcessorMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[CoversClass(NewEntryListener::class)]
#[UsesClass(NewEntryProcessorMessage::class)]
#[UsesClass(NewEntryAwardMessage::class)]
final class NewEntryListenerTest extends TestCase
{
    public function testNotifyTriggersNotifierForPostRequestWithEntry(): void
    {
        $entryIri = '/api/entries/1';
        $userIri = '/api/users/1';
        $entryContent = 'Test content';

        /** @var Entry&MockObject $entry */
        $entry = $this->createMock(Entry::class);
        $entry->method('getContent')->willReturn($entryContent);

        /** @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $entry->method('getUser')->willReturn($user);

        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $entry);

        /** @var MessageBusInterface&MockObject $bus */
        $bus = $this->createMock(MessageBusInterface::class);

        $dispatchedMessages = [];
        $bus->method('dispatch')->willReturnCallback(static function (object $message) use (&$dispatchedMessages) {
            $dispatchedMessages[] = $message;

            return new Envelope($message);
        });

        /** @var IriConverterInterface&MockObject $iriConverter */
        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getIriFromResource')->willReturnMap([
            [$entry, $entryIri],
            [$user, $userIri],
        ]);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $loggerMessages = [];
        $logger->method('debug')->willReturnCallback(static function (string $message, array $context = []) use (&$loggerMessages) {
            $loggerMessages[] = [$message, $context];
        });

        $listener = new NewEntryListener($bus, $iriConverter, $logger);
        $listener($event);

        // Assert messages
        self::assertCount(2, $dispatchedMessages);
        self::assertInstanceOf(NewEntryProcessorMessage::class, $dispatchedMessages[0]);
        self::assertSame($entryIri, $dispatchedMessages[0]->entryIri);
        self::assertSame($entryContent, $dispatchedMessages[0]->content);

        self::assertInstanceOf(NewEntryAwardMessage::class, $dispatchedMessages[1]);
        self::assertSame($entryIri, $dispatchedMessages[1]->entryIri);
        self::assertSame($userIri, $dispatchedMessages[1]->userIri);

        // Assert logs
        self::assertCount(2, $loggerMessages);
        self::assertSame('New entry notified to processors', $loggerMessages[0][0]);
        self::assertSame(['entry' => $entryIri, 'user' => $userIri], $loggerMessages[0][1]);

        self::assertSame('New entry notified to awards system', $loggerMessages[1][0]);
        self::assertSame(['entry' => $entryIri, 'user' => $userIri], $loggerMessages[1][1]);
    }

    public function testNotifyDoesNothingForNonPostRequest(): void
    {
        /** @var Entry&MockObject $entry */
        $entry = $this->createMock(Entry::class);
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET]);

        /** @var HttpKernelInterface&MockObject $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $entry);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->expects(self::never())->method('getIriFromResource');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $listener = new NewEntryListener($bus, $iriConverter, $logger);
        $listener($event);
    }

    public function testNotifyDoesNothingForNonEntryControllerResult(): void
    {
        $nonEntryResult = new \stdClass();
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);

        /** @var HttpKernelInterface&MockObject $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $nonEntryResult);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->expects(self::never())->method('getIriFromResource');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('info');

        $listener = new NewEntryListener($bus, $iriConverter, $logger);
        $listener($event);
    }
}
