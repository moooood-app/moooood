<?php

declare(strict_types=1);

namespace App\MessageHandler;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Entry;
use App\Message\ProcessorOutputMessage;
use App\Repository\EntryMetadataRepository;
use App\Repository\EntryRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[AsMessageHandler]
final class ProcessorOutputMessageHandler
{
    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly EntryMetadataRepository $entryMetadataRepository,
        private readonly LoggerInterface $logger,
        private readonly TagAwareCacheInterface $cache,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    public function __invoke(ProcessorOutputMessage $message): void
    {
        /** @var Entry */
        $entry = $this->iriConverter->getResourceFromIri($message->entryIri);
        $this->logger->info('Data received from processor {processor} for entry {entry}', [
            'entry' => $entry->getId()->toString(),
            'processor' => $message->processor->value,
        ]);

        $this->entryRepository->removeExistingMetadataForProcessor($entry, $message->processor);

        $this->entryMetadataRepository->createMetadataFromProcessorOutput($entry, $message);

        $this->cache->invalidateTags([\sprintf('user-metrics-%s', $entry->getUser()->getId()->toString())]);
    }
}
