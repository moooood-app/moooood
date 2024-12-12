<?php

declare(strict_types=1);

namespace App\Notifier;

use App\Message\Awards\AwardEventMessageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class AwardEventNotifier
{
    public function __construct(
        private readonly TexterInterface $texter,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly MessageOptionsInterface $awardEventsSnsOptions,
    ) {
    }

    public function notify(AwardEventMessageInterface $awardEventMessage): void
    {
        $payload = $this->serializer->serialize($awardEventMessage, 'jsonld', [
            AbstractNormalizer::GROUPS => [AwardEventMessageInterface::SERIALIZATION_GROUP_SNS],
            'jsonld_has_context' => false,
        ]);

        $message = new ChatMessage($payload, $this->awardEventsSnsOptions);

        try {
            $this->texter->send($message);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send SNS notification: '.$e->getMessage());
        }
    }
}
