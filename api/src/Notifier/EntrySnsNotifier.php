<?php

namespace App\Notifier;

use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Psr\Log\LoggerInterface;
use App\Entity\Entry;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;

class EntrySnsNotifier
{
    public function __construct(
        private readonly TexterInterface $texter,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly MessageOptionsInterface $entrySnsOptions
    ) {}

    public function notify(Entry $entry): void
    {
        $payload = $this->serializer->serialize($entry, 'jsonld', [
            AbstractNormalizer::GROUPS => [Entry::SERIALIZATION_GROUP_SNS],
        ]);

        $message = new ChatMessage($payload, $this->entrySnsOptions);

        try {
            $this->texter->send($message);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send SNS notification: ' . $e->getMessage());
        }
    }
}
