<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Enum\Metrics\GroupingCriteria;
use App\Message\ProcessorOutputMessage;
use App\Repository\EntryMetadataRepository;
use App\Repository\EntryRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;

#[AsMessageHandler]
final class ProcessorOutputMessageHandler
{
    /** @todo inject the EntryMetadataRepository instead here */
    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly EntryMetadataRepository $entryMetadataRepository,
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
    }

    public function __invoke(ProcessorOutputMessage $message): void
    {
        $this->logger->info('Data received from processor {processor} for entry {entry}', [
            'entry' => $message->getEntry()->getId(),
            'processor' => $message->getProcessor()->value,
        ]);

        $entry = $message->getEntry();

        $this->entryRepository->removeExistingMetadataForProcessor($entry, $message->getProcessor());

        $this->entryMetadataRepository->createMetadataFromProcessorOutput($entry, $message);

        foreach (GroupingCriteria::cases() as $groupingCriteria) {
            $this->cache->delete("{$message->getProcessor()->value}_metrics_{$entry->getUser()->getId()}_{$groupingCriteria->value}");
        }
    }
}
