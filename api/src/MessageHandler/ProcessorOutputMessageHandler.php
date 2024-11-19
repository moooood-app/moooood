<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\EntryMetadata;
use App\Enum\Metrics\GroupingCriteria;
use App\Message\ProcessorOutputMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;

#[AsMessageHandler]
final class ProcessorOutputMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
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

        $existingMetadata = $entry->getMetadata()->filter(static fn (EntryMetadata $metadata) => $metadata->getProcessor() === $message->getProcessor());
        if ($existingMetadata->count() > 0) {
            $this->logger->warning('Metadata for processor {processor} already exists for entry {entry}, replacing it', [
                'entry' => $message->getEntry()->getId(),
                'processor' => $message->getProcessor()->value,
            ]);

            foreach ($existingMetadata as $metadata) {
                $entry->removeMetadata($metadata);
                $this->entityManager->remove($metadata);
            }
            $this->entityManager->flush();
        }

        $metadata = new EntryMetadata();
        $metadata->setProcessor($message->getProcessor());
        $metadata->setMetadata($message->getResult());

        $entry->addMetadata($metadata);

        $this->entityManager->persist($metadata);
        $this->entityManager->flush();

        foreach (GroupingCriteria::cases() as $groupingCriteria) {
            $this->cache->delete("{$message->getProcessor()->value}_metrics_{$entry->getUser()->getId()}_{$groupingCriteria->value}");
        }
    }
}
