<?php

namespace App\Tests\Integration\Metrics;

use App\DataFixtures\UserFixtures;
use App\Enum\Metrics\MetricsGrouping;
use App\Metadata\Metrics\MetricsApiResource;
use App\Tests\Integration\Traits\AuthenticatedClientTrait;
use App\Tests\Integration\Traits\ValidateJsonSchemaTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
abstract class AbstractMetricsTestCase extends WebTestCase
{
    use AuthenticatedClientTrait;
    use MetricsTypeProviderTrait;
    use ValidateJsonSchemaTrait;

    #[DataProvider('provideQueryParametersPerMetricsType')]
    public function testMetricsAreCorrectlyReturnedWhenUserIsAuthenticated(
        string $metricsType,
        MetricsGrouping $groupingCriteria,
        ?\DateTime $dateFrom = null,
        bool $groupByParts = false,
    ): void {
        $parameters = [
            MetricsApiResource::GROUPING_FILTER_KEY => $groupingCriteria->value,
            MetricsApiResource::GROUP_BY_PARTS_FILTER_KEY => $groupByParts ? '1' : '0',
        ];

        if (null !== $dateFrom) {
            $parameters[MetricsApiResource::FROM_DATE_FILTER_KEY] = $dateFrom->format('Y-m-d');
        }

        $client = self::createAuthenticatedClient(UserFixtures::FIRST_USER);

        $client->request(Request::METHOD_GET, "/api/metrics/{$metricsType}", $parameters, [], [
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
         *   member: array<object{
         *     "@id": string,
         *     "@type": string,
         *     date: string,
         *   }>,
         *   view: object,
         *   search: object,
         * } $data
         */
        $data = json_decode($content);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertSame("/api/metrics/{$metricsType}", $data->{'@id'});
        self::assertSame('/api/contexts/'.ucfirst($metricsType), $data->{'@context'});
        self::assertSame('Collection', $data->{'@type'});
        self::assertJsonSchemaIsValid($data, \sprintf('metrics/%s.json', $metricsType));

        $this->assertResponseIsValid($data);
    }

    /**
     * @return iterable<array{0: string, 1: MetricsGrouping, 2?: \DateTime|null, 3?: bool|null}>
     */
    public static function provideQueryParametersPerMetricsType(): iterable
    {
        foreach (static::provideMetricsType() as [$metricsType]) {
            foreach (static::provideQueryParameters() as $scenario => $params) {
                yield "{$metricsType}: {$scenario}" => array_merge([$metricsType], $params); // @phpstan-ignore-line
            }
        }
    }

    /**
     * @return iterable<string, array{0: MetricsGrouping, 1?: \DateTime|null, 2?: bool|null}>
     */
    abstract public static function provideQueryParameters(): iterable;

    /**
     * @param object{
     *   "@id": string,
     *   "@context": string,
     *   "@type": string,
     *   totalItems: int,
     *   member: array<object{
     *     "@id": string,
     *     "@type": string,
     *     date: string,
     *   }>,
     *   view: object,
     *   search: object,
     * } $data
     */
    abstract protected function assertResponseIsValid(object $data): void;
}
