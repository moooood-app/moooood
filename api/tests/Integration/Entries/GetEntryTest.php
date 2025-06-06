<?php

namespace App\Tests\Integration\Entries;

use App\DataFixtures\UserFixtures;
use App\Doctrine\CurrentUserExtension;
use App\Entity\Entry;
use App\Entity\User;
use App\EventListener\NewEntryListener;
use App\EventListener\TokenCreatedListener;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\EntryRepository;
use App\Repository\UserRepository;
use App\Tests\Integration\Traits\AuthenticatedClientTrait;
use App\Tests\Integration\Traits\ValidateJsonSchemaTrait;
use App\Tests\Integration\Traits\ValidationErrorsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(Entry::class)]
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(CurrentUserExtension::class)]
#[CoversClass(EntryRepository::class)]
#[CoversClass(NewEntryListener::class)]
#[UsesClass(MetricsApiResource::class)]
#[UsesClass(TokenCreatedListener::class)]
final class GetEntryTest extends WebTestCase
{
    use AuthenticatedClientTrait;
    use ValidateJsonSchemaTrait;
    use ValidationErrorsTrait;

    public function testRequestIsRejectedWhenUserNotAuthenticated(): void
    {
        $client = self::createClient();

        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);

        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        /** @var Entry */
        $entry = $user->getEntries()->first();

        $client->request(Request::METHOD_GET, "/api/entries/{$entry->getId()}");

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testEntryIsReturnedWhenUserAuthenticated(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);

        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);

        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        /** @var Entry */
        $entry = $user->getEntries()->first();
        $client->request(Request::METHOD_GET, "/api/entries/{$entry->getId()}");

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        /** @var object */
        $data = json_decode($content);

        self::assertJsonSchemaIsValid($data, 'entries/entry.json');
    }

    public function testUserCanOnlySeeTheirOwnEntry(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::HACKER_USER);

        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);

        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        /** @var Entry */
        $entry = $user->getEntries()->first();
        $client->request(Request::METHOD_GET, "/api/entries/{$entry->getId()}");

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
