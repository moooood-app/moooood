<?php

namespace App\Tests\Integration\Entries;

use App\DataFixtures\UserFixtures;
use App\Entity\Entry;
use App\Entity\User;
use App\EventListener\NewEntryListener;
use App\EventListener\TokenCreatedListener;
use App\Message\Awards\NewEntryAwardMessage;
use App\Message\NewEntryProcessorMessage;
use App\Messenger\Transport\SnsTransportFactory;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\UserRepository;
use App\Schedule;
use App\Scheduler\AwardsScheduler;
use App\Tests\Integration\Traits\AuthenticatedClientTrait;
use App\Tests\Integration\Traits\ValidationErrorsTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Messenger\DataCollector\MessengerDataCollector;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

/**
 * @internal
 */
#[CoversClass(Entry::class)]
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(NewEntryListener::class)]
#[UsesClass(MetricsApiResource::class)]
#[UsesClass(TokenCreatedListener::class)]
#[UsesClass(NewEntryAwardMessage::class)]
#[UsesClass(NewEntryProcessorMessage::class)]
#[UsesClass(SnsTransportFactory::class)]
#[UsesClass(Schedule::class)]
#[UsesClass(AwardsScheduler::class)]
final class CreateEntryTest extends WebTestCase
{
    use AuthenticatedClientTrait;
    use InteractsWithMessenger;
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
        ], $jsonPayload);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

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

        if (!$client->getProfile() instanceof Profile) {
            self::fail('Profiler not enabled');
        }

        /** @var MessengerDataCollector */
        $messsengerCollector = $client->getProfile()->getCollector('messenger');

        $queue = $this->transport('new-entry')->queue();
        $queue->assertCount(1);
        $queue->assertContains(NewEntryProcessorMessage::class, 1);

        $queue->first(static function (NewEntryProcessorMessage $m) use ($data): bool {
            return $m->content === $data['content'] && $m->entryIri === $data['@id'];
        });

        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);
        /** @var User */
        $user = $repository->findOneBy(['email' => UserFixtures::FIRST_USER]);

        $queue = $this->transport('award-events')->queue();
        $queue->assertCount(1);
        $queue->assertContains(NewEntryAwardMessage::class, 1);

        $queue->first(static function (NewEntryAwardMessage $m) use ($data, $user): bool {
            return $m->userIri === \sprintf('/api/users/%s', $user->getId()) && $m->entryIri === $data['@id'];
        });
    }

    public function testValidationErrorForLongContent(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);

        /** @var non-empty-string $jsonPayload */
        $jsonPayload = json_encode([
            'content' => str_repeat('a', 5001),
        ]);

        $client->request(Request::METHOD_POST, '/api/entries', [], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ], $jsonPayload);

        self::assertValidationErrors($client, [
            [
                'propertyPath' => 'content',
                'message' => 'An entry cannot be longer than 5000 characters',
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
