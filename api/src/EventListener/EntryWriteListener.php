<?php

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Entry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Notifier\Bridge\AmazonSns\AmazonSnsOptions;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class EntryWriteListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly TexterInterface $texter,
        private readonly SerializerInterface $serializer,
        private readonly ParameterBagInterface $parameterBag,
        private readonly LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['notify', EventPriorities::POST_WRITE],
        ];
    }

    public function notify(ViewEvent $event): void
    {
        $entry = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$entry  || Request::METHOD_POST !== $method) {
            return;
        }

        $payload = $this->serializer->serialize($entry, 'jsonld', [
            AbstractNormalizer::GROUPS => [Entry::GROUP_NOTIFICATION],
        ]);

        $options = new AmazonSnsOptions($this->parameterBag->get('new_entry_sns_arn'));
        // $options->messageStructure('json');

        $message = new ChatMessage($payload, $options);

        try {
            $this->texter->send($message);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('An error occurred while sending the SNS notification: '.$e->getMessage());
        }
    }
}
