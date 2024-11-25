<?php

namespace App\Tests\Integration\Metrics;

use App\DataFixtures\UserFixtures;
use App\Tests\Integration\Traits\AuthenticatedClientTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
abstract class AbstractMetricsTestCase extends WebTestCase
{
    use AuthenticatedClientTrait;

    abstract protected function getMetricsName(): string;

    /**
     * @param object{totalItems: int, member: array<mixed>} $data
     */
    abstract protected function assertResponseIsValid(object $data): void;

    public function testRequestIsRejectedWhenUserIsNotAuthenticated(): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, '/api/metrics/'.$this->getMetricsName(), ['grouping' => 'day'], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testMetricsAreCorrectlyReturnedWhenUserIsAuthenticated(): void
    {
        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);

        $client->request(Request::METHOD_GET, '/api/metrics/'.$this->getMetricsName(), ['grouping' => 'day'], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        /**
         * @var object{
         *   "@id": string,
         *   "@context": string,
         *   "@type": string,
         *   totalItems: int,
         *   member: array<mixed>,
         *   view: object,
         *   search: object,
         * } $data
         */
        $data = json_decode($content);

        self::assertSame('/api/metrics/'.$this->getMetricsName(), $data->{'@id'});
        self::assertSame('/api/contexts/'.ucfirst($this->getMetricsName()), $data->{'@context'});
        self::assertSame('Collection', $data->{'@type'});

        $this->assertResponseIsValid($data);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
