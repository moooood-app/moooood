<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use App\Entity\Entry;
use App\Entity\EntryMetadata;
use App\Entity\User;
use App\Enum\Metrics\GroupingCriteria;
use App\Message\ProcessorOutputMessage;
use App\MessageHandler\ProcessorOutputMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Enum\Processor;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\CacheInterface;

final class ProcessorOutputMessageHandlerTest extends TestCase
{
    /**
     * @covers \App\MessageHandler\ProcessorOutputMessageHandler::__invoke
     */
    public function testInvokeLogsAndPersistMetadata(): void
    {
        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var Entry&MockObject $entry */
        $entry = $this->createPartialMock(Entry::class, ['getId']);
        $entry->__construct();
        $entry->method('getId')->willReturn(new Uuid('00000000-0000-0000-0000-000000000000'));
        /** @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(new Uuid('09e6c349-fb5c-4f9c-8b05-d434f00e4b73'));
        $entry->setUser($user);

        $processor = Processor::SENTIMENT; // Use an actual processor enum value
        $message = new ProcessorOutputMessage($entry, ['some' => 'result'], $processor);

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->exactly(count(GroupingCriteria::cases())))
            ->method('delete')
            ->with($this->callback(function(string $key): bool {
                return in_array($key, [
                    'sentiment_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_entry',
                    'sentiment_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_hour',
                    'sentiment_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_day',
                    'sentiment_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_week',
                    'sentiment_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_month',
                    'sentiment_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_year',
                ]);
            }));

        $handler = new ProcessorOutputMessageHandler($entityManager, $logger, $cache);

        $logger->expects($this->once())
            ->method('info')
            ->with(
                'Data received from processor {processor} for entry {entry}',
                [
                    'entry' => '00000000-0000-0000-0000-000000000000',
                    'processor' => $processor->value,
                ],
            );

        $entryMetadata = new EntryMetadata();
        $entryMetadata
            ->setProcessor($processor)
            ->setMetadata(['some' => 'result'])
        ;

        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(
                static function (EntryMetadata $metadata){
                    return $metadata->getProcessor() === Processor::SENTIMENT
                        && $metadata->getMetadata() === ['some' => 'result'];
                },
        ));
        $entityManager->expects($this->once())->method('flush');

        $handler($message);

        $this->assertCount(1, $entry->getMetadata()->filter(static function (EntryMetadata $metadata) use ($processor) {
            return $metadata->getProcessor() === $processor && $metadata->getMetadata() === ['some' => 'result'];
        }));
    }

    /**
     * @covers \App\MessageHandler\ProcessorOutputMessageHandler::__invoke
     */
    public function testInvokeReplacesExistingMetadataForProcessor(): void
    {
        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var Entry&MockObject $entry */
        $entry = $this->createPartialMock(Entry::class, ['getId']);
        $entry->__construct();
        $entry->method('getId')->willReturn(new Uuid('00000000-0000-0000-0000-000000000000'));
        /** @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(new Uuid('09e6c349-fb5c-4f9c-8b05-d434f00e4b73'));
        $entry->setUser($user);


        $processor = Processor::COMPLEXITY;
        $message = new ProcessorOutputMessage($entry, ['new' => 'data'], $processor);

        /** @var CacheInterface&MockObject $cache */
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->exactly(count(GroupingCriteria::cases())))
            ->method('delete')
            ->with($this->callback(function(string $key): bool {
                return in_array($key, [
                    'complexity_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_entry',
                    'complexity_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_hour',
                    'complexity_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_day',
                    'complexity_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_week',
                    'complexity_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_month',
                    'complexity_metrics_09e6c349-fb5c-4f9c-8b05-d434f00e4b73_year',
                ]);
            }));

        $handler = new ProcessorOutputMessageHandler($entityManager, $logger, $cache);

        $existingMetadata = new EntryMetadata();
        $existingMetadata->setProcessor($processor);
        $existingMetadata->setMetadata(['old' => 'data']);

        $entry->addMetadata($existingMetadata);

        $entityManager->expects($this->once())
            ->method('remove')
            ->with($existingMetadata);

        $logger->expects($this->once())
            ->method('warning')
            ->with(
                'Metadata for processor {processor} already exists for entry {entry}, replacing it',
                [
                    'entry' => '00000000-0000-0000-0000-000000000000',
                    'processor' => $processor->value,
                ]
            );

        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(EntryMetadata::class));

        $entityManager->expects($this->once())->method('remove')->with($existingMetadata);
        $entityManager->expects($this->exactly(2))->method('flush');

        $this->assertCount(1, $entry->getMetadata()->filter(static function (EntryMetadata $metadata) use ($processor) {
            return $metadata->getProcessor() === $processor && $metadata->getMetadata() === ['old' => 'data'];
        }));

        $handler($message);

        $this->assertCount(1, $entry->getMetadata()->filter(static function (EntryMetadata $metadata) use ($processor) {
            return $metadata->getProcessor() === $processor && $metadata->getMetadata() === ['new' => 'data'];
        }));
    }

}
