<?php

declare(strict_types=1);

namespace App\Tests\Messenger\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Entry;
use App\Enum\Processor;
use App\Message\ProcessorOutputMessage;
use App\Messenger\Serializer\ProcessorOutputDecoder;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * @covers \App\Messenger\Serializer\ProcessorOutputDecoder
 */
class ProcessorOutputDecoderTest extends TestCase
{
    public function testDecode(): void
    {
        /** @var IriConverterInterface&MockObject $iriConverter */
        $iriConverter = $this->createMock(IriConverterInterface::class);

        /** @var DecoderInterface&MockObject $decoder */
        $decoder = $this->createMock(DecoderInterface::class);

        $entry = $this->createMock(Entry::class);

        $encodedEnvelope = [
            'body' => '{"Message":"{\"@id\":\"/entries/123\",\"result\":{\"key\":\"value\"},\"processor\":\"sentiment\"}"}',
        ];

        $decoder->expects($this->exactly(2))
            ->method('decode')
            ->willReturnCallback(function (string $body, string $format) use ($encodedEnvelope) {
                $this->assertSame(JsonEncoder::FORMAT, $format);
                $message = '{"@id":"/entries/123","result":{"key":"value"},"processor":"sentiment"}';

                return match ($body) {
                    $encodedEnvelope['body'] => [
                        'Message' => $message,
                    ],
                    $message => [
                        '@id' => '/entries/123',
                        'result' => ['key' => 'value'],
                        'processor' => 'sentiment',
                    ],
                    default => $this->fail('Unexpected decode input.'),
                };
            });

        $iriConverter->expects($this->once())
            ->method('getResourceFromIri')
            ->with('/entries/123')
            ->willReturn($entry);

        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);

        $decoderInstance = new ProcessorOutputDecoder($iriConverter, $decoder, $logger);

        $envelope = $decoderInstance->decode($encodedEnvelope);

        $this->assertInstanceOf(ProcessorOutputMessage::class, $envelope->getMessage());

        /** @var ProcessorOutputMessage $message */
        $message = $envelope->getMessage();
        $this->assertSame($entry, $message->getEntry());
        $this->assertSame(['key' => 'value'], $message->getResult());
        $this->assertSame(Processor::SENTIMENT, $message->getProcessor());
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
}
