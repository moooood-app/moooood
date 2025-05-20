<?php

namespace App\MessageHandler;

use App\Message\NewEntryProcessorMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class NewEntryEventMessageHandler
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
        private readonly string $inferenceApiUrl
    ) {}

    public function __invoke(NewEntryProcessorMessage $message): void
    {
        $response = $this->client->request(Request::METHOD_POST, $this->inferenceApiUrl, [
            'json' => ['entry' => $message->content],
        ]);

        $this->bus->dispatch(new NewEntryProcessorMessage($message->entryIri, $response->getContent(true)));
        $this->logger->info('New entry processed', [
            'entry' => $message->entryIri,
        ]);
    }
}
