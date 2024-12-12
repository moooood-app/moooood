<?php

declare(strict_types=1);

namespace App\Messenger\Serializer;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use App\Message\Awards\AwardEventMessageInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AwardEventDecoder implements SerializerInterface
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly DecoderInterface $decoder,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array{body: string} $encodedEnvelope
     */
    public function decode(array $encodedEnvelope): Envelope
    {
        /** @var array{Message: string} $body */
        $body = $this->decoder->decode($encodedEnvelope['body'], JsonEncoder::FORMAT);

        $this->logger->info('Decoding message {message}', ['message' => $body['Message']]);

        /** @var array{"@type": string} $message */
        $message = $this->decoder->decode($body['Message'], JsonEncoder::FORMAT);

        $type = 'App\Message\Awards\\'.$message['@type'];
        if (!class_exists($type)) {
            throw new \InvalidArgumentException('Invalid message type');
        }

        try {
            /** @var AwardEventMessageInterface */
            $event = $this->denormalizer->denormalize($message, $type, 'jsonld', [
                AbstractNormalizer::GROUPS => [AwardEventMessageInterface::SERIALIZATION_GROUP_SNS],
            ]);
        } catch (ItemNotFoundException $e) {
            $this->logger->error('Could not denormalize the message');
            throw $e;
        }

        return new Envelope($event);
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
