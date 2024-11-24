<?php

namespace App\Tests\Integration\Entry;

use App\DataFixtures\UserFixtures;
use App\Entity\Entry;
use App\Entity\User;
use App\EventListener\EntryWriteListener;
use App\Metadata\Metrics\MetricsApiResource;
use App\Notifier\EntrySnsNotifier;
use App\Repository\UserRepository;
use App\Tests\Integration\Traits\AuthenticatedClientTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(Entry::class)]
#[CoversClass(User::class)]
#[CoversClass(EntryWriteListener::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(EntrySnsNotifier::class)]
#[CoversClass(MetricsApiResource::class)]
final class CreateEntryTest extends WebTestCase
{
    use AuthenticatedClientTrait;

    public function testRequestIsRejectedWhenUserNotAuthenticated(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_POST, '/api/entries', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], '{}',
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @todo test validation
     */
    public function testEntryIsCreatedWhenUserAuthenticated(): void
    {
        $client = $this->createAuthenticatedClient(UserFixtures::FIRST_USER);

        /** @var non-empty-string $jsonPayload */
        $jsonPayload = json_encode([
            'content' => 'This is a test',
        ]);

        $client->request(Request::METHOD_POST, '/api/entries', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], $jsonPayload,
        );

        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        /**
         * @var array{
         *   "@id": string,
         *   "@context": string,
         *   "@type": string,
         *   content: string,
         *   metadata: array<mixed>,
         *   createdAt: ?string,
         *   updatedAt: ?string,
         * } $data
         */
        $data = json_decode($content, true);

        self::assertStringStartsWith('/api/entries/', $data['@id']);
        self::assertSame('/api/contexts/Entry', $data['@context']);
        self::assertSame('Entry', $data['@type']);
        self::assertSame('This is a test', $data['content']);
        self::assertSame([], $data['metadata']);
        self::assertNotNull($data['createdAt']);
        self::assertNotNull($data['updatedAt']);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }
}
