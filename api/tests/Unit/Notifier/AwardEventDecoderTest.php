<?php

declare(strict_types=1);

namespace App\Tests\Unit\Messenger\Serializer;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use App\Doctrine\CurrentUserExtension;
use App\Entity\Entry;
use App\Message\Awards\NewEntryEventMessage;
use App\Messenger\Serializer\AwardEventDecoder;
use App\Repository\EntryRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @internal
 */
#[CoversClass(AwardEventDecoder::class)]
#[CoversClass(NewEntryEventMessage::class)]
#[UsesClass(CurrentUserExtension::class)]
#[UsesClass(EntryRepository::class)]
final class AwardEventDecoderTest extends KernelTestCase
{
    public function testDecodeNewEntryEventMessage(): void
    {
        self::bootKernel();

        /** @var DenormalizerInterface */
        $denormalizer = self::getContainer()->get(DenormalizerInterface::class);
        /** @var DecoderInterface */
        $decoder = self::getContainer()->get(DecoderInterface::class);
        /** @var LoggerInterface&MockObject */
        $logger = $this->createMock(LoggerInterface::class);

        $entry = self::getEntry();
        $user = $entry->getUser();

        $encodedEnvelope = [
            'body' => \sprintf(
                '{"Message":"{\"@type\":\"NewEntryEventMessage\",\"entry\":\"/api/entries/%s\",\"user\":\"/api/users/%s\"}"}',
                $entry->getId()->toString(),
                $user->getId()->toString(),
            ),
        ];

        /** @var array{Message: string} */
        $decodedMessage = json_decode($encodedEnvelope['body'], true);

        $logger
            ->expects(self::once())
            ->method('info')
            ->with('Decoding message {message}', ['message' => $decodedMessage['Message']])
        ;

        $decoderInstance = new AwardEventDecoder($denormalizer, $decoder, $logger);

        $envelope = $decoderInstance->decode($encodedEnvelope);

        self::assertInstanceOf(NewEntryEventMessage::class, $envelope->getMessage());
    }

    public static function getEntry(): Entry
    {
        $container = self::getContainer();

        /** @var EntryRepository */
        $entryRepository = $container->get(EntryRepository::class);

        return $entryRepository->createQueryBuilder('p') // @phpstan-ignore-line
            ->select('p')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    public function testDecodeThrowsInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid message type');

        /** @var DenormalizerInterface&MockObject */
        $denormalizer = $this->createMock(DenormalizerInterface::class);
        /** @var DecoderInterface&MockObject */
        $decoder = $this->createMock(DecoderInterface::class);
        /** @var LoggerInterface&MockObject */
        $logger = $this->createMock(LoggerInterface::class);

        $encodedEnvelope = [
            'body' => '{"Message":"{\"@type\":\"InvalidMessageType\"}"}',
        ];

        $decodedBody = ['Message' => '{"@type":"InvalidMessageType"}'];
        $decodedMessage = ['@type' => 'InvalidMessageType'];

        $decoder
            ->expects(self::exactly(2))
            ->method('decode')
            ->willReturnOnConsecutiveCalls($decodedBody, $decodedMessage)
        ;

        $decoderInstance = new AwardEventDecoder($denormalizer, $decoder, $logger);

        $decoderInstance->decode($encodedEnvelope);
    }

    public function testDecodeWritesAnErrorLogForResourceNotFound(): void
    {
        $this->expectException(ItemNotFoundException::class);
        $this->expectExceptionMessage('Resource not found');

        /** @var DenormalizerInterface&MockObject */
        $denormalizer = $this->createMock(DenormalizerInterface::class);
        /** @var DecoderInterface */
        $decoder = self::getContainer()->get(DecoderInterface::class);
        /** @var LoggerInterface&MockObject */
        $logger = $this->createMock(LoggerInterface::class);

        $denormalizer
            ->method('denormalize')
            ->willThrowException(new ItemNotFoundException('Resource not found'))
        ;

        $encodedEnvelope = [
            'body' => '{"Message":"{\"@type\":\"NewEntryEventMessage\",\"entry\":\"/api/entries/1\",\"user\":\"/api/users/1\"}"}',
        ];

        /** @var array{Message: string} */
        $decodedMessage = json_decode($encodedEnvelope['body'], true);

        $logger
            ->expects(self::once())
            ->method('info')
            ->with('Decoding message {message}', ['message' => $decodedMessage['Message']])
        ;

        $logger
            ->expects(self::once())
            ->method('error')
            ->with('Could not denormalize the message')
        ;

        $decoderInstance = new AwardEventDecoder($denormalizer, $decoder, $logger);

        $decoderInstance->decode($encodedEnvelope);
    }

    public function testEncodeThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This serializer is only used for decoding messages.');

        /** @var DenormalizerInterface&MockObject */
        $denormalizer = $this->createMock(DenormalizerInterface::class);
        /** @var DecoderInterface&MockObject */
        $decoder = $this->createMock(DecoderInterface::class);
        /** @var LoggerInterface&MockObject */
        $logger = $this->createMock(LoggerInterface::class);

        $decoderInstance = new AwardEventDecoder($denormalizer, $decoder, $logger);

        $decoderInstance->encode(new Envelope(new \stdClass()));
    }
}
