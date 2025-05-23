<?php

namespace App\MessageHandler;

use App\Enum\Processor;
use App\Message\NewEntryProcessorMessage;
use App\Message\ProcessorOutputMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class NewEntryProcessorMessageHandler
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
        private readonly string $inferenceApiUrl,
        private readonly string $processorName,
    ) {
    }

    public function __invoke(NewEntryProcessorMessage $message): void
    {
        $response = $this->client->request(Request::METHOD_POST, $this->inferenceApiUrl, [
            'json' => ['entry' => $message->content],
        ]);

        $output = new ProcessorOutputMessage(
            $message->entryIri,
            $response->toArray(false),
            Processor::from($this->processorName),
        );

        $this->bus->dispatch($output);
        $this->logger->info('New entry processed', [
            'entry' => $output->entryIri,
        ]);
    }
}
