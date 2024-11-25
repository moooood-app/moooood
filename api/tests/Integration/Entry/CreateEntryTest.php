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
use App\Tests\Integration\Traits\ValidationErrorsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Notifier\DataCollector\NotificationDataCollector;
use Symfony\Component\Notifier\Message\ChatMessage;

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
    use ValidationErrorsTrait;

    public function testRequestIsRejectedWhenUserNotAuthenticated(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_POST, '/api/entries', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], '{}');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testEntryIsCreatedWhenUserAuthenticated(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);
        $client->enableProfiler();

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

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        if (!$client->getProfile() instanceof Profile) {
            self::fail('Profiler not enabled');
        }

        /** @var NotificationDataCollector */
        $notifierCollector = $client->getProfile()->getCollector('notifier');

        self::assertCount(1, $notifierCollector->getEvents()->getMessages());
        $message = $notifierCollector->getEvents()->getMessages()[0];
        self::assertInstanceOf(ChatMessage::class, $message);
        /** @var array<string, mixed> */
        $payload = json_decode($message->getSubject(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertEqualsCanonicalizing([
            '@context' => '/api/contexts/Entry',
            '@id' => $data['@id'],
            '@type' => 'Entry',
            'content' => 'This is a test',
        ], $payload);
    }

    public function testValidationErrorForLongContent(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);

        /** @var non-empty-string $jsonPayload */
        $jsonPayload = json_encode([
            'content' => str_repeat('a', 1001),
        ]);

        $client->request(Request::METHOD_POST, '/api/entries', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], $jsonPayload);

        self::assertValidationErrors($client, [
            [
                'propertyPath' => 'content',
                'message' => 'An entry cannot be longer than 1000 characters',
            ],
        ]);
    }

    public function testValidationErrorForShortContent(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);

        /** @var non-empty-string $jsonPayload */
        $jsonPayload = json_encode([
            'content' => str_repeat('a', 9),
        ]);

        $client->request(Request::METHOD_POST, '/api/entries', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], $jsonPayload);

        self::assertValidationErrors($client, [
            [
                'propertyPath' => 'content',
                'message' => 'An entry must be at least 10 characters long',
            ],
        ]);
    }
}
