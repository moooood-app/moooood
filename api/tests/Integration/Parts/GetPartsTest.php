<?php

namespace App\Tests\Integration\Entries;

use ApiPlatform\Metadata\IriConverterInterface;
use App\DataFixtures\UserFixtures;
use App\Doctrine\CurrentUserExtension;
use App\Entity\Part;
use App\Entity\User;
use App\EventListener\EntryWriteListener;
use App\EventListener\TokenCreatedListener;
use App\Metadata\Metrics\MetricsApiResource;
use App\Notifier\EntrySnsNotifier;
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
#[CoversClass(EntryWriteListener::class)]
#[CoversClass(EntrySnsNotifier::class)]
#[CoversClass(CurrentUserExtension::class)]
#[UsesClass(MetricsApiResource::class)]
#[UsesClass(TokenCreatedListener::class)]
final class GetPartsTest extends WebTestCase
{
    use AuthenticatedClientTrait;
    use ValidateJsonSchemaTrait;
    use ValidationErrorsTrait;

    public function testRequestIsRejectedWhenUserNotAuthenticated(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/api/parts');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testPartsAreReturnedWhenUserAuthenticated(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);
        $client->request(Request::METHOD_GET, '/api/parts');

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

        $partsIris = array_map(
            static function (object $part): string {
                return $part->{'@id'}; // @phpstan-ignore-line
            },
            $data->member,
        );

        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);

        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        /** @var PartRepository */
        $partRepository = self::getContainer()->get(PartRepository::class);

        $partRepository->findBy(['user' => $user]);

        /** @var IriConverterInterface */
        $iriConverter = self::getContainer()->get(IriConverterInterface::class);
        $userParts = array_map(
            static function (Part $part) use ($iriConverter): string {
                return $iriConverter->getIriFromResource($part); // @phpstan-ignore-line
            },
            $partRepository->findBy(['user' => $user])
        );

        sort($partsIris);
        sort($userParts);
        self::assertSame($userParts, $partsIris);

        self::assertJsonSchemaIsValid($data, 'parts/parts.json');
    }
}
