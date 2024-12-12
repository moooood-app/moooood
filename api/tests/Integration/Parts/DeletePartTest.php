<?php

namespace App\Tests\Integration\Entries;

use App\DataFixtures\UserFixtures;
use App\Doctrine\CurrentUserExtension;
use App\Entity\Part;
use App\Entity\User;
use App\EventListener\NewEntryListener;
use App\EventListener\TokenCreatedListener;
use App\Metadata\Metrics\MetricsApiResource;
use App\Notifier\AwardEventNotifier;
use App\Notifier\EntryProcessorNotifier;
use App\Repository\EntryRepository;
use App\Repository\PartRepository;
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
#[CoversClass(Part::class)]
#[CoversClass(PartRepository::class)]
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(CurrentUserExtension::class)]
#[CoversClass(EntryRepository::class)]
#[CoversClass(NewEntryListener::class)]
#[CoversClass(EntryProcessorNotifier::class)]
#[CoversClass(AwardEventNotifier::class)]
#[UsesClass(MetricsApiResource::class)]
#[UsesClass(TokenCreatedListener::class)]
final class DeletePartTest extends WebTestCase
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

        /** @var Part */
        $part = $user->getParts()->first();

        $client->request(Request::METHOD_DELETE, "/api/parts/{$part->getId()}");

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testPartIsDeletedWhenUserAuthenticated(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);

        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);

        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        /** @var Part */
        $part = $user->getParts()->first();
        $client->request(Request::METHOD_DELETE, "/api/parts/{$part->getId()}");

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        self::assertEmpty($content);
    }

    public function testUserCannotDeleteAnotherUserPart(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::HACKER_USER);

        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);

        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        /** @var Part */
        $part = $user->getParts()->first();
        $client->request(Request::METHOD_DELETE, "/api/parts/{$part->getId()}");

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
