<?php

namespace App\Tests\Integration\Entry;

use App\DataFixtures\UserFixtures;
use App\Doctrine\CurrentUserExtension;
use App\Entity\User;
use App\EventListener\EntryWriteListener;
use App\Notifier\EntrySnsNotifier;
use App\Repository\UserRepository;
use App\Tests\Integration\Traits\AuthenticatedClientTrait;
use Doctrine\ORM\EntityManagerInterface;
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
final class PatchUserTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    public function testRequestIsRejectedWhenUserNotAuthenticated(): void
    {
        $client = self::createClient();

        /** @var User */
        $user = self::getContainer()->get(UserRepository::class)->findOneByEmail(UserFixtures::FIRST_USER);

        $client->request(Request::METHOD_PATCH, "/api/users/{$user->getId()}", [], [], [
            'CONTENT_TYPE' => 'application/merge-patch+json',
        ], '{}', );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @todo test validation
     */
    public function testUserCanEditTheirOwnInformation(): void
    {
        $client = $this->createAuthenticatedClient(UserFixtures::FIRST_USER);

        /** @var User */
        $user = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => UserFixtures::FIRST_USER]);

        /** @var non-empty-string $jsonPayload */
        $jsonPayload = json_encode($payload = [
            'id' => 'new-id',
            'firstName' => 'New first name',
            'lastName' => 'New last name',
            'email' => 'new-email@moooood.app',
            'password' => 'new-password',
            'google' => 'new-google',
            'apple' => 'new-apple',
        ]);

        $client->request(Request::METHOD_PATCH, "/api/users/{$user->getId()}", [], [], [
            'CONTENT_TYPE' => 'application/merge-patch+json',
        ], $jsonPayload, );

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

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertStringStartsWith("/api/users/{$user->getId()}", $data['@id']);
        self::assertSame('/api/contexts/User', $data['@context']);
        self::assertSame('User', $data['@type']);
        self::assertSame($payload['firstName'], $data['firstName']);
        self::assertSame($payload['lastName'], $data['lastName']);
        self::assertSame($payload['email'], $data['email']);
        self::assertArrayNotHasKey('password', $data);
        self::assertArrayNotHasKey('google', $data);
        self::assertArrayNotHasKey('apple', $data);

        /** @var EntityManagerInterface */
        $manager = self::getContainer()->get(EntityManagerInterface::class);
        $manager->refresh($user);

        self::assertNotSame($payload['id'], (string) $user->getId());
        self::assertNotSame($payload['password'], $user->getPassword());
        self::assertNotSame($payload['google'], $user->getGoogle());
        self::assertNotSame($payload['apple'], $user->getApple());
    }

    public function testUserCannotEditAnotherUserInformation(): void
    {
        $client = $this->createAuthenticatedClient(UserFixtures::HACKER_USER);

        /** @var User */
        $otherUser = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => UserFixtures::FIRST_USER]);

        $client->request(Request::METHOD_GET, "/api/users/{$otherUser->getId()}");

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}