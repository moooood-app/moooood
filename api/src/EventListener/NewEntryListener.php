<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Entry;
use App\Message\Awards\NewEntryEventMessage;
use App\Notifier\AwardEventNotifier;
use App\Notifier\EntryProcessorNotifier;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::VIEW, priority: EventPriorities::POST_WRITE)]
final class NewEntryListener
{
    public function __construct(
        private readonly EntryProcessorNotifier $notifier,
        private readonly AwardEventNotifier $awardEventNotifier,
    ) {
    }

    public function __invoke(ViewEvent $event): void
    {
        $entry = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$entry instanceof Entry || Request::METHOD_POST !== $method) {
            return;
        }

        $this->notifier->notify($entry);
        $this->awardEventNotifier->notify(new NewEntryEventMessage($entry));
    }
}
