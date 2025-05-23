<?php

namespace App\Messenger\Serializer;

use Psr\Log\LoggerInterface;
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
        private readonly LoggerInterface $logger,
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
         *     Message?: string,
         *     MessageAttributes?: array<string, array{
         *         Type: string,
         *         StringValue: string,
         *     }>,
         * } $payload */
        $payload = $this->decoder->decode($encodedEnvelope['body'], JsonEncoder::FORMAT);

        $data = $payload['Message'] ?? $encodedEnvelope['body'];

        $this->logger->debug('Received payload: {payload}', [
            'payload' => $payload,
        ]);

        /** @var object */
        $message = $this->serializer->deserialize(
            $data,
            $this->getMessageClass(),
            JsonEncoder::FORMAT,
        );

        return new Envelope($message);
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
