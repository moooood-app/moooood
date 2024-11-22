<?php

declare(strict_types=1);

namespace App\Tests\OAuth;

use App\Entity\User;
use App\OAuth\AccountConnector;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @internal
 */
#[CoversClass(AccountConnector::class)]
#[CoversClass(User::class)]
final class AccountConnectorTest extends TestCase
{
    public function testConnectAssignsOAuthAccountToUserAndPersists(): void
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $propertyAccessor = new PropertyAccessor();

        $resourceOwner = $this->createMock(ResourceOwnerInterface::class);
        $resourceOwner
            ->expects(self::once())
            ->method('getName')
            ->willReturn('google')
        ;

        $userResponse = new PathUserResponse();
        $userResponse->setPaths(['identifier' => 'google_id']);
        $userResponse->setData(['google_id' => 'google-unique-id']);
        $userResponse->setResourceOwner($resourceOwner);

        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(User::class))
        ;

        $entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $user = new User();
        $user->setFirstName('John')->setLastname('Doe')->setEmail('john.doe@example.com')->setPassword('password123');

        $accountConnector = new AccountConnector($entityManager, $propertyAccessor);

        // Act
        $accountConnector->connect($user, $userResponse);

        // Assert
        self::assertSame('google-unique-id', $user->getGoogle(), 'The Google account ID should be assigned to the user.');
    }
}
