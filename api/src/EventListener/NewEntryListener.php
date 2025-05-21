<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Entry;
use App\Message\Awards\NewEntryAwardMessage;
use App\Message\NewEntryProcessorMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener(event: KernelEvents::VIEW, priority: EventPriorities::POST_WRITE)]
final class NewEntryListener
{
    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly IriConverterInterface $iriConverter,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ViewEvent $event): void
    {
        $entry = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$entry instanceof Entry || Request::METHOD_POST !== $method) {
            return;
        }

        /** @var string */
        $entryIri = $this->iriConverter->getIriFromResource($entry);
        /** @var string */
        $userIri = $this->iriConverter->getIriFromResource($entry->getUser());

        $this->bus->dispatch(new NewEntryProcessorMessage($entryIri, $entry->getContent()));
        $this->logger->info('New entry notified to processors', [
            'entry' => $entryIri,
            'user' => $userIri,
        ]);

        $this->bus->dispatch(new NewEntryAwardMessage($entryIri, $userIri));
        $this->logger->info('New entry notified to awards system', [
            'entry' => $entryIri,
            'user' => $userIri,
        ]);
    }
}
