<?php

namespace App\Tests\Unit\EventListener;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\EventListener\TokenCreatedListener;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
#[CoversClass(TokenCreatedListener::class)]
#[UsesClass(UserRepository::class)]
#[UsesClass(MetricsApiResource::class)]
final class TokenCreatedListenerTest extends KernelTestCase
{
    public function testJwtContainsUserData(): void
    {
        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);

        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);
        $security = $this->createMock(Security::class);
        $security->expects(self::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        /** @var NormalizerInterface */
        $normalizer = self::getContainer()->get(NormalizerInterface::class);

        $listener = new TokenCreatedListener($security, $normalizer);

        $event = new JWTCreatedEvent([], $user);
        $listener->onJwtCreated($event);

        self::assertSame(
            [
                '@context' => '/api/contexts/User',
                '@id' => "/api/users/{$user->getId()}",
                '@type' => 'User',
                'id' => $user->getId()->toString(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
            ],
            $event->getData(),
        );
    }
}
