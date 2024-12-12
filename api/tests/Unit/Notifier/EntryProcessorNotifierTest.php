<?php

declare(strict_types=1);

namespace App\Tests\Notifier;

use App\Entity\Entry;
use App\Metadata\Metrics\MetricsApiResource;
use App\Notifier\EntryProcessorNotifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[CoversClass(EntryProcessorNotifier::class)]
#[CoversClass(Entry::class)]
#[CoversClass(MetricsApiResource::class)]
final class EntryProcessorNotifierTest extends KernelTestCase
{
    public function testNotifySuccess(): void
    {
        /** @var TexterInterface&MockObject $texter */
        $texter = $this->createMock(TexterInterface::class);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var MessageOptionsInterface&MockObject $entrySnsOptions */
        $entrySnsOptions = $this->createMock(MessageOptionsInterface::class);

        $container = self::getContainer();

        /** @var SerializerInterface $serializer */
        $serializer = $container->get(SerializerInterface::class);

        $notifier = new EntryProcessorNotifier($texter, $serializer, $logger, $entrySnsOptions);

        $entry = new Entry();
        $this->setProperty($entry, 'id', new Uuid('09e6c349-fb5c-4f9c-8b05-d434f00e4b73'));
        $entry->setContent('Test Entry');

        $serializedEntry = $serializer->serialize($entry, 'jsonld', [
            AbstractNormalizer::GROUPS => ['entry:sns'],
        ]);

        $texter
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (ChatMessage $message) use ($serializedEntry, $entrySnsOptions) {
                return $message->getSubject() === $serializedEntry
                       && $message->getOptions() === $entrySnsOptions;
            }))
        ;

        $logger->expects(self::never())->method('error');

        $notifier->notify($entry);
    }

    public function testNotifyFails(): void
    {
        /** @var TexterInterface&MockObject $texter */
        $texter = $this->createMock(TexterInterface::class);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var MessageOptionsInterface&MockObject $entrySnsOptions */
        $entrySnsOptions = $this->createMock(MessageOptionsInterface::class);

        $container = self::getContainer();

        /** @var SerializerInterface $serializer */
        $serializer = $container->get(SerializerInterface::class);

        $notifier = new EntryProcessorNotifier($texter, $serializer, $logger, $entrySnsOptions);

        $entry = new Entry();
        $this->setProperty($entry, 'id', new Uuid('09e6c349-fb5c-4f9c-8b05-d434f00e4b73'));
        $entry->setContent('Test Entry');

        /** @var TransportExceptionInterface&MockObject $exception */
        $exception = $this->createMock(TransportExceptionInterface::class);
        $texter
            ->expects(self::once())
            ->method('send')
            ->willThrowException($exception)
        ;

        $logger
            ->expects(self::once())
            ->method('error')
            ->with(self::stringContains('Failed to send SNS notification'))
        ;

        $notifier->notify($entry);
    }

    private function setProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
