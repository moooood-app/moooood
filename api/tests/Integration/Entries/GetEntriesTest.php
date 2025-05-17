<?php

namespace App\Tests\Integration\Entries;

use ApiPlatform\Metadata\IriConverterInterface;
use App\DataFixtures\UserFixtures;
use App\Doctrine\CurrentUserExtension;
use App\Entity\Entry;
use App\Entity\User;
use App\EventListener\NewEntryListener;
use App\EventListener\TokenCreatedListener;
use App\Metadata\Metrics\MetricsApiResource;
use App\Notifier\AwardEventNotifier;
use App\Notifier\EntryProcessorNotifier;
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
#[CoversClass(EntryProcessorNotifier::class)]
#[CoversClass(AwardEventNotifier::class)]
#[UsesClass(MetricsApiResource::class)]
#[UsesClass(TokenCreatedListener::class)]
final class GetEntriesTest extends WebTestCase
{
    use AuthenticatedClientTrait;
    use ValidateJsonSchemaTrait;
    use ValidationErrorsTrait;

    public function testRequestIsRejectedWhenUserNotAuthenticated(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/api/entries');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testEntriesAreReturnedWhenUserAuthenticated(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);
        $client->request(Request::METHOD_GET, '/api/entries');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        /**
         * @var object{
         *  search: object{template: non-empty-string},
         *  totalItems: int,
         *  member: list<object{'@id': string}>,
         * }
         */
        $data = json_decode($content);

        self::assertGreaterThan(0, $data->totalItems ?? 0);

        self::assertJsonSchemaIsValid($data, 'entries/entries.json');

        self::assertSame(
            '/api/entries{?createdAt[before],createdAt[strictly_before],createdAt[after],createdAt[strictly_after]}',
            $data->search->template,
        );

        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);

        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        self::assertSame($user->getEntries()->count(), $data->totalItems);

        /** @var IriConverterInterface */
        $iriConverter = self::getContainer()->get(IriConverterInterface::class);
        foreach ($data->member as $entry) {
            /** @var string */
            $iri = $entry->{'@id'};
            /** @var Entry */
            $e = $iriConverter->getResourceFromIri($iri);
            self::assertTrue($user->getId()->equals($e->getUser()->getId()));
        }
    }
}
