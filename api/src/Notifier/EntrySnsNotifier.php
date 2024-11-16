<?php

declare(strict_types=1);

namespace App\Notifier;

use App\Entity\Entry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class EntrySnsNotifier
{
    public function __construct(
        private readonly TexterInterface $texter,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly MessageOptionsInterface $entrySnsOptions,
    ) {
    }

    public function notify(Entry $entry): void
    {
        $payload = $this->serializer->serialize($entry, 'jsonld', [
            AbstractNormalizer::GROUPS => [Entry::SERIALIZATION_GROUP_SNS],
        ]);

        $message = new ChatMessage($payload, $this->entrySnsOptions);

        try {
            $this->texter->send($message);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send SNS notification: '.$e->getMessage());
        }
    }
}
