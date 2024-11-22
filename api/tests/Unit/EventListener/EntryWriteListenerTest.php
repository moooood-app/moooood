<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\Entity\Entry;
use App\EventListener\EntryWriteListener;
use App\Notifier\EntrySnsNotifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 */
#[CoversClass(EntryWriteListener::class)]
final class EntryWriteListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = EntryWriteListener::getSubscribedEvents();
        self::assertArrayHasKey('kernel.view', $events);
        self::assertSame(['notify', 31], $events['kernel.view']); // EventPriorities::POST_WRITE = 32
    }

    public function testNotifyTriggersNotifierForPostRequestWithEntry(): void
    {
        /** @var EntrySnsNotifier&MockObject $notifier */
        $notifier = $this->createMock(EntrySnsNotifier::class);
        $listener = new EntryWriteListener($notifier);

        /** @var Entry&MockObject $entry */
        $entry = $this->createMock(Entry::class);
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);

        /** @var HttpKernelInterface&MockObject $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $entry);

        $notifier->expects(self::once())
            ->method('notify')
            ->with(self::identicalTo($entry))
        ;

        $listener->notify($event);
    }

    public function testNotifyDoesNothingForNonPostRequest(): void
    {
        /** @var EntrySnsNotifier&MockObject $notifier */
        $notifier = $this->createMock(EntrySnsNotifier::class);
        $listener = new EntryWriteListener($notifier);

        /** @var Entry&MockObject $entry */
        $entry = $this->createMock(Entry::class);
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_GET]);

        /** @var HttpKernelInterface&MockObject $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $entry);

        $notifier->expects(self::never())->method('notify');

        $listener->notify($event);
    }

    public function testNotifyDoesNothingForNonEntryControllerResult(): void
    {
        /** @var EntrySnsNotifier&MockObject $notifier */
        $notifier = $this->createMock(EntrySnsNotifier::class);
        $listener = new EntryWriteListener($notifier);

        $nonEntryResult = new \stdClass();
        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => Request::METHOD_POST]);

        /** @var HttpKernelInterface&MockObject $kernel */
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $nonEntryResult);

        $notifier->expects(self::never())->method('notify');

        $listener->notify($event);
    }
}
