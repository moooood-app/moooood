<?php

namespace App\Tests\Integration\Entries;

use App\DataFixtures\UserFixtures;
use App\Entity\Part;
use App\Entity\User;
use App\EventListener\EntryWriteListener;
use App\EventListener\TokenCreatedListener;
use App\Metadata\Metrics\MetricsApiResource;
use App\Notifier\EntrySnsNotifier;
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
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(EntryWriteListener::class)]
#[CoversClass(EntrySnsNotifier::class)]
#[UsesClass(MetricsApiResource::class)]
#[UsesClass(TokenCreatedListener::class)]
final class CreatePartTest extends WebTestCase
{
    use AuthenticatedClientTrait;
    use ValidateJsonSchemaTrait;
    use ValidationErrorsTrait;

    public function testRequestIsRejectedWhenUserNotAuthenticated(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_POST, '/api/parts', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], '{}');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testPartIsCreatedWhenUserAuthenticated(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);
        $client->enableProfiler();

        /** @var non-empty-string $jsonPayload */
        $jsonPayload = json_encode([
            'name' => 'Test Part',
            'colors' => ['#000000', '#FFFFFF', '#FF0000', '#00FF00', '#0000FF'],
        ]);

        $client->request(Request::METHOD_POST, '/api/parts', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], $jsonPayload);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        /**
         * @var object{
         *   "@id": string,
         *   "@context": string,
         *   "@type": string,
         *   name: string,
         *   colors: array<mixed>,
         *   createdAt: ?string,
         *   updatedAt: ?string,
         * } $data
         */
        $data = json_decode($content);

        /** @var non-empty-string $iri */
        $iri = $data->{'@id'};
        self::assertStringStartsWith('/api/parts/', $iri);
        self::assertSame('/api/contexts/Part', $data->{'@context'});
        self::assertSame('Part', $data->{'@type'});
        self::assertSame('Test Part', $data->name);
        self::assertSame(['#000000', '#FFFFFF', '#FF0000', '#00FF00', '#0000FF'], $data->colors);
        self::assertNotNull($data->createdAt);
        self::assertNotNull($data->updatedAt);

        self::assertJsonSchemaIsValid($data, 'parts/part.json');
    }
}
