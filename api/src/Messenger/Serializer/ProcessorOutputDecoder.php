<?php

declare(strict_types=1);

namespace App\Messenger\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
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

    public function decode(array $encodedEnvelope): Envelope
    {
        $body = $this->decoder->decode($encodedEnvelope['body'], JsonEncoder::FORMAT);

        $message = $this->decoder->decode($body['Message'], JsonEncoder::FORMAT);

        $processorOutputMessage = new ProcessorOutputMessage(
            $this->iriConverter->getResourceFromIri($message['@id']),
            $message['result'],
            Processor::from($message['processor']),
        );

        return new Envelope($processorOutputMessage);
    }

    public function encode(Envelope $envelope): array
    {
        // this decoder does not encode messages, but you can implement it by returning
        // an array with serialized stamps if you need to send messages in a custom format
        throw new \LogicException('This serializer is only used for decoding messages.');
    }
}
