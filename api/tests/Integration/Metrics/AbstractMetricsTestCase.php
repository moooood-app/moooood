<?php

namespace App\Tests\Integration\Metrics;

use App\Tests\Integration\Traits\AuthenticatedClientTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
abstract class AbstractMetricsTestCase extends WebTestCase
{
    use AuthenticatedClientTrait;

    abstract protected function getMetricsName(): string;

    /**
     * @param array<string, mixed> $data
     */
    abstract protected function assertResponseIsValid(array $data): void;

    public function testRequestIsRejectedWhenUserNotAuthenticated(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/api/metrics/'.$this->getMetricsName(), ['grouping' => 'day'], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testEntryIsCreatedWhenUserAuthenticated(): void
    {
        $client = $this->createAuthenticatedClient('peter@forcepure.com');

        $client->request(Request::METHOD_GET, '/api/metrics/'.$this->getMetricsName(), ['grouping' => 'day'], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        /**
         * @var array{
         *   "@id": string,
         *   "@context": string,
         *   "@type": string,
         *   totalItems: int,
         *   member: array<mixed>,
         *   view: object,
         *   search: object,
         * } $data
         */
        $data = json_decode($content, true);

        self::assertSame('/api/metrics/'.$this->getMetricsName(), $data['@id']);
        self::assertSame('/api/contexts/'.ucfirst($this->getMetricsName()), $data['@context']);
        self::assertSame('Collection', $data['@type']);

        $this->assertResponseIsValid($data);

        $this->assertResponseIsSuccessful();
    }
}
