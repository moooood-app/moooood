<?php

namespace App\Messenger\Serializer;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

abstract class AbstractSerializer implements SerializerInterface
{
    public function __construct(
        #[Autowire(service: 'messenger.transport.symfony_serializer')]
        private readonly SerializerInterface $decorated,
        private readonly DecoderInterface $decoder,
        private readonly SymfonySerializerInterface $serializer,
    ) {
    }

    /**
     * @return class-string
     */
    abstract protected function getMessageClass(): string;

    /**
     * @param array{
     *     body: string,
     *     headers: array<string, string>,
     * } $encodedEnvelope
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        /**
         * @var array{
         *     Message: string,
         *     MessageAttributes: array<string, array{
         *         Type: string,
         *         StringValue: string,
         *     }>,
         * } $payload */
        $payload = $this->decoder->decode($encodedEnvelope['body'], JsonEncoder::FORMAT);

        /** @var object */
        $message = $this->serializer->deserialize(
            $payload['Message'],
            $this->getMessageClass(),
            JsonEncoder::FORMAT,
        );

        return new Envelope(
            $message,
            // todo manage the stamps correctly
        );
    }

    /**
     * @return array{
     *     body: string,
     *     headers: array<string, string>,
     * }
     */
    public function encode(Envelope $envelope): array
    {
        return $this->decorated->encode($envelope); // @phpstan-ignore-line
    }
}
