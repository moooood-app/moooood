<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Entry;
use App\Notifier\EntrySnsNotifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class EntryWriteListener implements EventSubscriberInterface
{
    public function __construct(private readonly EntrySnsNotifier $notifier)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['notify', EventPriorities::POST_WRITE],
        ];
    }

    public function notify(ViewEvent $event): void
    {
        $entry = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$entry instanceof Entry || Request::METHOD_POST !== $method) {
            return;
        }

        $this->notifier->notify($entry);
    }
}
