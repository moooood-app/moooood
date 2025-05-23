<?php

declare(strict_types=1);

namespace App\Messenger\Transport;

use AsyncAws\Sns\SnsClient;
use AsyncAws\Sns\ValueObject\MessageAttributeValue;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final class SnsTransport implements TransportInterface
{
    public function __construct(
        private readonly SnsClient $sns,
        private readonly SerializerInterface $serializer,
        private readonly string $topic,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function send(Envelope $envelope): Envelope
    {
        /**
         * @var array{
         *    body: string,
         *    headers: ?array<string, string>,
         * }
         */
        $encodedMessage = $this->serializer->encode($envelope);
        $this->logger->debug('SNS message: {encodedMessage}', [
            'encodedMessage' => $encodedMessage['body'],
        ]);

        $headers = ($encodedMessage['headers'] ?? []) + [
            'type' => $envelope->getMessage()::class,
        ];

        $arguments = [
            'MessageAttributes' => [
                'Headers' => new MessageAttributeValue(['DataType' => 'String', 'StringValue' => json_encode($headers, \JSON_THROW_ON_ERROR)]),
            ],
            'Message' => $encodedMessage['body'],
            'TopicArn' => $this->topic,
        ];

        if (str_ends_with($this->topic, '.fifo')) {
            $stamps = $envelope->all();
            $dedupeStamp = $stamps[SnsFifoStamp::class][0] ?? false;
            if (!$dedupeStamp || false === $dedupeStamp instanceof SnsFifoStamp) {
                throw new \Exception('SnsFifoStamp required for fifo topic');
            }
            $messageGroupId = $dedupeStamp->getMessageGroupId() ?? false;
            $messageDeDuplicationId = $dedupeStamp->getMessageDeduplicationId() ?? false;
            if ($messageDeDuplicationId) {
                $arguments['MessageDeduplicationId'] = $messageDeDuplicationId;
            }
            if ($messageGroupId) {
                $arguments['MessageGroupId'] = $messageGroupId;
            }
        }

        try {
            $result = $this->sns->publish($arguments);
            $messageId = $result->getMessageId();
        } catch (\Throwable $e) {
            throw new TransportException($e->getMessage(), 0, $e);
        }

        if (null === $messageId) {
            throw new TransportException('Could not add a message to the SNS topic');
        }

        return $envelope;
    }

    public function get(): iterable
    {
        throw new \Exception('Not implemented');
    }

    public function ack(Envelope $envelope): void
    {
        throw new \Exception('Not implemented');
    }

    public function reject(Envelope $envelope): void
    {
        throw new \Exception('Not implemented');
    }
}
