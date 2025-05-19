<?php

declare(strict_types=1);

namespace App\Messenger\Transport;

use AsyncAws\Sns\SnsClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @implements TransportFactoryInterface<SnsTransport>
 */
final class SnsTransportFactory implements TransportFactoryInterface
{
    public function __construct(private readonly SnsClient $sns, private readonly LoggerInterface $logger)
    {
    }

    /**
     * @param array<mixed> $options
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        return new SnsTransport($this->sns, $serializer, mb_substr($dsn, 6), $this->logger);
    }

    /**
     * @param array<mixed> $options
     */
    public function supports(string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'sns://arn:aws:sns:');
    }
}
