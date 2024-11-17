<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\Entry;
use App\EventListener\EntryWriteListener;
use App\Notifier\EntrySnsNotifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class EntryWriteListenerTest extends TestCase
{
    /**
     * @covers \App\EventListener\EntryWriteListener::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $events = EntryWriteListener::getSubscribedEvents();
        $this->assertArrayHasKey('kernel.view', $events);
        $this->assertSame(['notify', 31], $events['kernel.view']); // EventPriorities::POST_WRITE = 32
    }

    /**
     * @covers \App\EventListener\EntryWriteListener::notify
     */
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

        $notifier->expects($this->once())
            ->method('notify')
            ->with($this->identicalTo($entry));

        $listener->notify($event);
    }

    /**
     * @covers \App\EventListener\EntryWriteListener::notify
     */
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

        $notifier->expects($this->never())->method('notify');

        $listener->notify($event);
    }

    /**
     * @covers \App\EventListener\EntryWriteListener::notify
     */
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

        $notifier->expects($this->never())->method('notify');

        $listener->notify($event);
    }
}
