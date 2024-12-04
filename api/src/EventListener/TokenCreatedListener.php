<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class TokenCreatedListener
{
    public function __construct(
        private NormalizerInterface $normalizer,
    ) {
    }

    #[AsEventListener(event: Events::JWT_CREATED)]
    public function onJwtCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        /** @var array<string, mixed> */
        $normalizedUser = $this->normalizer->normalize($user, 'jsonld', ['groups' => [User::SERIALIZATION_GROUP_JWT]]);
        $payload = array_merge(
            $event->getData(),
            $normalizedUser,
        );
        $event->setData($payload);
    }
}
