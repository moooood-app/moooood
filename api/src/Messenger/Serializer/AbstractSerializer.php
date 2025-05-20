<?php

namespace App\Messenger\Serializer;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

abstract class AbstractSerializer implements SerializerInterface
{
    public function __construct(
        #[Autowire(service: 'messenger.transport.symfony_serializer')]
        private readonly SerializerInterface $decorated,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @return class-string
     */
    abstract protected function getMessageClass(): string;

    public function decode(array $encodedEnvelope): Envelope
    {
        $encodedEnvelope['headers']['type'] = $encodedEnvelope['headers']['type'] ?? $this->getMessageClass();

        return $this->decorated->decode($encodedEnvelope);
    }

    public function encode(Envelope $envelope): array
    {
        return $this->decorated->encode($envelope);
    }
}
