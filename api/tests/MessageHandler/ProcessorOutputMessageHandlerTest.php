<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\MessageHandler\ProcessorOutputMessageHandler;
use App\Message\ProcessorOutputMessage;
use App\Enum\Metrics\GroupingCriteria;
use App\Entity\Entry;
use App\Entity\User;
use App\Enum\Processor;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use App\Repository\EntryRepository;
use App\Repository\EntryMetadataRepository;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProcessorOutputMessageHandlerTest extends TestCase
{
    public function testMessageHandling(): void
    {
        /** @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(new Uuid('09e6c349-fb5c-4f9c-8b05-d434f00e4b73'));

        /** @var Entry&MockObject $entry */
        $entry = $this->createPartialMock(Entry::class, ['getId']);
        $entry->__construct();
        $entry->method('getId')->willReturn(new Uuid('00000000-0000-0000-0000-000000000000'));
        $entry->setUser($user);

        $processor = Processor::COMPLEXITY;
        $message = new ProcessorOutputMessage($entry, ['new' => 'data'], $processor);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var EntryRepository&MockObject $entryRepository */
        $entryRepository = $this->createMock(EntryRepository::class);
        $entryRepository->expects($this->once())
            ->method('removeExistingMetadataForProcessor')
            ->with($entry, $message->getProcessor());

        /** @var EntryMetadataRepository&MockObject $entryMetadataRepository */
        $entryMetadataRepository = $this->createMock(EntryMetadataRepository::class);
        $entryMetadataRepository->expects($this->once())
            ->method('createMetadataFromProcessorOutput')
            ->with($entry, $message);

        /** @var TagAwareCacheInterface&MockObject $cache */
        $cache = $this->createMock(TagAwareCacheInterface::class);

        $cache->expects($this->once())
            ->method('invalidateTags')
            ->with(['user-metrics-09e6c349-fb5c-4f9c-8b05-d434f00e4b73']);

        // Create the handler and invoke it
        $handler = new ProcessorOutputMessageHandler(
            $entryRepository,
            $entryMetadataRepository,
            $logger,
            $cache
        );

        $handler->__invoke($message);
    }
}
