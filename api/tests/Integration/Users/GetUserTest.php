<?php

namespace App\Tests\Integration\Entry;

use App\DataFixtures\UserFixtures;
use App\Doctrine\CurrentUserExtension;
use App\Entity\User;
use App\EventListener\EntryWriteListener;
use App\Notifier\EntrySnsNotifier;
use App\Repository\UserRepository;
use App\Tests\Integration\Traits\AuthenticatedClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
#[UsesClass(CurrentUserExtension::class)]
#[UsesClass(EntryWriteListener::class)]
#[UsesClass(EntrySnsNotifier::class)]
final class GetUserTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    public function testRequestIsRejectedWhenUserNotAuthenticated(): void
    {
        $client = self::createClient();

        /** @var User */
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail(UserFixtures::FIRST_USER);

        $client->request(Request::METHOD_GET, "/api/users/{$user->getId()}");

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUserCanAccessTheirOwnInformation(): void
    {
        $client = $this->createAuthenticatedClient(UserFixtures::FIRST_USER);

        /** @var User */
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => UserFixtures::FIRST_USER]);

        $client->request(Request::METHOD_GET, "/api/users/{$user->getId()}");

        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        /**
         * @var array{
         *   "@id": string,
         *   "@context": string,
         *   "@type": string,
         *   firstName: string,
         *   lastName: string,
         *   email: string,
         * } $data
         */
        $data = json_decode($content, true);

        $this->assertResponseIsSuccessful();

        self::assertStringStartsWith("/api/users/{$user->getId()}", $data['@id']);
        self::assertSame('/api/contexts/User', $data['@context']);
        self::assertSame('User', $data['@type']);
        self::assertSame($user->getFirstName(), $data['firstName']);
        self::assertSame($user->getLastname(), $data['lastName']);
        self::assertSame($user->getEmail(), $data['email']);
        self::assertArrayNotHasKey('password', $data);
        self::assertArrayNotHasKey('google', $data);
        self::assertArrayNotHasKey('apple', $data);
    }

    public function testUserCannotAccessAnotherUserInformation(): void
    {
        $client = $this->createAuthenticatedClient(UserFixtures::HACKER_USER);

        /** @var User */
        $otherUser = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => UserFixtures::FIRST_USER]);

        $client->request(Request::METHOD_GET, "/api/users/{$otherUser->getId()}");

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
