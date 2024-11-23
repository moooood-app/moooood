<?php

declare(strict_types=1);

namespace App\Tests\Unit\Messenger\Serializer;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Entry;
use App\Enum\Processor;
use App\Message\ProcessorOutputMessage;
use App\Messenger\Serializer\ProcessorOutputDecoder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * @internal
 */
#[CoversClass(ProcessorOutputDecoder::class)]
#[CoversClass(ProcessorOutputMessage::class)]
final class ProcessorOutputDecoderTest extends KernelTestCase
{
    public function testDecode(): void
    {
        self::bootKernel();

        /** @var IriConverterInterface&MockObject $iriConverter */
        $iriConverter = $this->createMock(IriConverterInterface::class);

        /** @var DecoderInterface $decoder */
        $decoder = self::getContainer()->get(DecoderInterface::class);

        $entry = $this->createMock(Entry::class);

        $encodedEnvelope = [
            'body' => '{"Message":"{\"@id\":\"/entries/123\",\"result\":{\"key\":\"value\"},\"processor\":\"sentiment\"}"}',
        ];

        $iriConverter->expects(self::once())
            ->method('getResourceFromIri')
            ->with('/entries/123')
            ->willReturn($entry)
        ;

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        $decoderInstance = new ProcessorOutputDecoder($iriConverter, $decoder, $logger);

        $envelope = $decoderInstance->decode($encodedEnvelope);

        self::assertInstanceOf(ProcessorOutputMessage::class, $envelope->getMessage());

        /** @var ProcessorOutputMessage $message */
        $message = $envelope->getMessage();
        self::assertSame($entry, $message->getEntry());
        self::assertSame(['key' => 'value'], $message->getResult());
        self::assertSame(Processor::SENTIMENT, $message->getProcessor());
    }

    public function testEncodeThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('This serializer is only used for decoding messages.');

        /** @var IriConverterInterface&MockObject $iriConverter */
        $iriConverter = $this->createMock(IriConverterInterface::class);

        /** @var DecoderInterface&MockObject $decoder */
        $decoder = $this->createMock(DecoderInterface::class);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        $decoderInstance = new ProcessorOutputDecoder($iriConverter, $decoder, $logger);

        $decoderInstance->encode(new Envelope(new \stdClass()));
    }

    public function testIriConverterThrowsNotFoundException(): void
    {
        self::bootKernel();

        $this->expectException(ItemNotFoundException::class);
        $this->expectExceptionMessage('Item Not Found');

        /** @var IriConverterInterface&MockObject $iriConverter */
        $iriConverter = $this->createMock(IriConverterInterface::class);

        /** @var DecoderInterface $decoder */
        $decoder = self::getContainer()->get(DecoderInterface::class);

        $encodedEnvelope = [
            'body' => '{"Message":"{\"@id\":\"/entries/123\",\"result\":{\"key\":\"value\"},\"processor\":\"sentiment\"}"}',
        ];

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        $logger
            ->expects(self::once())
            ->method('error')
            ->with('Entry not found for IRI {iri}', ['iri' => '/entries/123'])
        ;

        $outputDecoder = new ProcessorOutputDecoder($iriConverter, $decoder, $logger);

        $iriConverter->expects(self::once())
            ->method('getResourceFromIri')
            ->with('/entries/123')
            ->willThrowException(new ItemNotFoundException('Item Not Found'))
        ;

        $outputDecoder->decode($encodedEnvelope);
    }
}
