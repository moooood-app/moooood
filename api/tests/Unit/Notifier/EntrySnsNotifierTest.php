<?php

declare(strict_types=1);

namespace App\Tests\Notifier;

use App\Entity\Entry;
use App\Metadata\Metrics\MetricsApiResource;
use App\Notifier\EntrySnsNotifier;
use PHPUnit\Framework\Attributes\CoversClass;
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
#[CoversClass(EntrySnsNotifier::class)]
#[CoversClass(Entry::class)]
#[CoversClass(MetricsApiResource::class)]
final class EntrySnsNotifierTest extends KernelTestCase
{
    public function testNotifySuccess(): void
    {
        /** @var TexterInterface&\PHPUnit\Framework\MockObject\MockObject $texter */
        $texter = $this->createMock(TexterInterface::class);

        /** @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var MessageOptionsInterface&\PHPUnit\Framework\MockObject\MockObject $entrySnsOptions */
        $entrySnsOptions = $this->createMock(MessageOptionsInterface::class);

        $container = self::getContainer();

        /** @var SerializerInterface $serializer */
        $serializer = $container->get(SerializerInterface::class);

        $notifier = new EntrySnsNotifier($texter, $serializer, $logger, $entrySnsOptions);

        $entry = new Entry();
        $this->setProperty($entry, 'id', new Uuid('09e6c349-fb5c-4f9c-8b05-d434f00e4b73')); // Set the id using PropertyAccess
        $entry->setContent('Test Entry'); // Assuming Entry has a setMessage method

        // Serialize the entry directly
        $serializedEntry = $serializer->serialize($entry, 'jsonld', [
            AbstractNormalizer::GROUPS => ['entry:sns'],
        ]);

        // Assert that Texter sends the correct message
        $texter
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (ChatMessage $message) use ($serializedEntry, $entrySnsOptions) {
                return $message->getSubject() === $serializedEntry
                       && $message->getOptions() === $entrySnsOptions;
            }))
        ;

        // Logger should not be called in this case
        $logger->expects(self::never())->method('error');

        $notifier->notify($entry);
    }

    public function testNotifyFails(): void
    {
        /** @var TexterInterface&\PHPUnit\Framework\MockObject\MockObject $texter */
        $texter = $this->createMock(TexterInterface::class);

        /** @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        /** @var MessageOptionsInterface&\PHPUnit\Framework\MockObject\MockObject $entrySnsOptions */
        $entrySnsOptions = $this->createMock(MessageOptionsInterface::class);

        $container = self::getContainer();

        /** @var SerializerInterface $serializer */
        $serializer = $container->get(SerializerInterface::class);

        $notifier = new EntrySnsNotifier($texter, $serializer, $logger, $entrySnsOptions);

        $entry = new Entry();
        $this->setProperty($entry, 'id', new Uuid('09e6c349-fb5c-4f9c-8b05-d434f00e4b73')); // Set the id using PropertyAccess
        $entry->setContent('Test Entry'); // Assuming Entry has a setMessage method

        // Stub Texter to throw an exception
        $texter
            ->expects(self::once())
            ->method('send')
            ->willThrowException($this->createMock(TransportExceptionInterface::class))
        ;

        // Logger should log the error
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
