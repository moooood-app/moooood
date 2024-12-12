<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\Entity\Entry;
use App\EventListener\NewEntryListener;
use App\Message\Awards\NewEntryEventMessage;
use App\Notifier\AwardEventNotifier;
use App\Notifier\EntryProcessorNotifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 */
#[CoversClass(NewEntryListener::class)]
#[CoversClass(NewEntryEventMessage::class)]
final class NewEntryListenerTest extends TestCase
{
    public function testNotifyTriggersNotifierForPostRequestWithEntry(): void
    {
        /** @var EntryProcessorNotifier&MockObject */
        $entryNotifier = $this->createMock(EntryProcessorNotifier::class);
        /** @var AwardEventNotifier&MockObject */
        $awardNotifier = $this->createMock(AwardEventNotifier::class);

        $listener = new NewEntryListener($entryNotifier, $awardNotifier);

        /** @var Entry&MockObject $entry */
        $entry = $this->createMock(Entry::class);
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);

        /** @var HttpKernelInterface&MockObject $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $entry);

        $entryNotifier->expects(self::once())
            ->method('notify')
            ->with($entry)
        ;

        $awardNotifier->expects(self::once())
            ->method('notify')
            ->with(self::callback(static function ($message) use ($entry): bool {
                return $message instanceof NewEntryEventMessage && $message->getEntry() === $entry;
            }))
        ;

        $listener($event);
    }

    public function testNotifyDoesNothingForNonPostRequest(): void
    {
        /** @var EntryProcessorNotifier&MockObject */
        $entryNotifier = $this->createMock(EntryProcessorNotifier::class);
        /** @var AwardEventNotifier&MockObject */
        $awardNotifier = $this->createMock(AwardEventNotifier::class);

        $listener = new NewEntryListener($entryNotifier, $awardNotifier);

        /** @var Entry&MockObject $entry */
        $entry = $this->createMock(Entry::class);
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET]);

        /** @var HttpKernelInterface&MockObject $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $entry);

        $entryNotifier->expects(self::never())->method('notify');
        $awardNotifier->expects(self::never())->method('notify');

        $listener($event);
    }

    public function testNotifyDoesNothingForNonEntryControllerResult(): void
    {
        /** @var EntryProcessorNotifier&MockObject */
        $entryNotifier = $this->createMock(EntryProcessorNotifier::class);
        /** @var AwardEventNotifier&MockObject */
        $awardNotifier = $this->createMock(AwardEventNotifier::class);

        $listener = new NewEntryListener($entryNotifier, $awardNotifier);

        $nonEntryResult = new \stdClass();
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);

        /** @var HttpKernelInterface&MockObject $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $nonEntryResult);

        $entryNotifier->expects(self::never())->method('notify');
        $awardNotifier->expects(self::never())->method('notify');

        $listener($event);
    }
}
