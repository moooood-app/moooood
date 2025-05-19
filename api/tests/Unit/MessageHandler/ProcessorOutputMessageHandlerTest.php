<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Entry;
use App\Entity\User;
use App\Enum\Processor;
use App\Message\ProcessorOutputMessage;
use App\MessageHandler\ProcessorOutputMessageHandler;
use App\Repository\EntryMetadataRepository;
use App\Repository\EntryRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * @internal
 */
#[CoversClass(ProcessorOutputMessageHandler::class)]
#[CoversClass(Entry::class)]
#[CoversClass(ProcessorOutputMessage::class)]
final class ProcessorOutputMessageHandlerTest extends TestCase
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
        $message = new ProcessorOutputMessage('/entries/123', ['new' => 'data'], $processor);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var EntryRepository&MockObject $entryRepository */
        $entryRepository = $this->createMock(EntryRepository::class);
        $entryRepository->expects(self::once())
            ->method('removeExistingMetadataForProcessor')
            ->with($entry, $message->processor)
        ;

        /** @var EntryMetadataRepository&MockObject $entryMetadataRepository */
        $entryMetadataRepository = $this->createMock(EntryMetadataRepository::class);
        $entryMetadataRepository->expects(self::once())
            ->method('createMetadataFromProcessorOutput')
            ->with($entry, $message)
        ;

        /** @var TagAwareCacheInterface&MockObject $cache */
        $cache = $this->createMock(TagAwareCacheInterface::class);

        $cache->expects(self::once())
            ->method('invalidateTags')
            ->with(['user-metrics-09e6c349-fb5c-4f9c-8b05-d434f00e4b73'])
        ;

        /** @var IriConverterInterface&MockObject $iriConverter */
        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->expects(self::once())
            ->method('getResourceFromIri')
            ->with($message->entryIri)
            ->willReturn($entry)
        ;

        // Create the handler and invoke it
        $handler = new ProcessorOutputMessageHandler(
            $entryRepository,
            $entryMetadataRepository,
            $logger,
            $cache,
            $iriConverter,
        );

        $handler->__invoke($message);
    }
}
