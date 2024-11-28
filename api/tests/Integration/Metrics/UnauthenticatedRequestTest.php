<?php

namespace App\Tests\Integration\Metrics;

use App\Tests\Integration\Traits\AuthenticatedClientTrait;
use App\Tests\Integration\Traits\ValidateJsonSchemaTrait;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversNothing]
final class UnauthenticatedRequestTest extends WebTestCase
{
    use AuthenticatedClientTrait;
    use MetricsTypeProviderTrait;
    use ValidateJsonSchemaTrait;

    #[DataProvider('provideMetricsType')]
    public function testRequestIsRejectedWhenUserIsNotAuthenticated(string $metricsType): void
    {
        $client = self::createClient();

        $client->request(Request::METHOD_GET, "/api/metrics/{$metricsType}", [
            'grouping' => 'day',
        ], [], [
            'CONTENT_TYPE' => 'application/ld+json',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
