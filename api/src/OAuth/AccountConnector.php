<?php

declare(strict_types=1);

namespace App\OAuth;

use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountConnector implements AccountConnectorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    public function connect(UserInterface $user, UserResponseInterface $response): void
    {
        $this->propertyAccessor->setValue(
            $user,
            $response->getResourceOwner()->getName(),
            $response->getUserIdentifier(),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
