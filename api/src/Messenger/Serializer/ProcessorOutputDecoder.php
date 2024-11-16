<?php

declare(strict_types=1);

namespace App\Messenger\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Entry;
use App\Enum\Processor;
use App\Message\ProcessorOutputMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class ProcessorOutputDecoder implements SerializerInterface
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly DecoderInterface $decoder,
    ) {
    }

    /**
     * @param array{body: string} $encodedEnvelope
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        /** @var array{Message: string} $body */
        $body = $this->decoder->decode($encodedEnvelope['body'], JsonEncoder::FORMAT);

        /** @var array{result: array<mixed>, "@id": string, processor: string} $message */
        $message = $this->decoder->decode($body['Message'], JsonEncoder::FORMAT);

        /** @var Entry $entry */
        $entry = $this->iriConverter->getResourceFromIri($message['@id']);
        $processorOutputMessage = new ProcessorOutputMessage(
            $entry,
            $message['result'],
            Processor::from($message['processor']),
        );

        return new Envelope($processorOutputMessage);
    }

    /**
     * @return array<mixed>
     */
    public function encode(Envelope $envelope): array
    {
        // this decoder does not encode messages, but you can implement it by returning
        // an array with serialized stamps if you need to send messages in a custom format
        throw new \LogicException('This serializer is only used for decoding messages.');
    }
}
