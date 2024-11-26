<?php

namespace App\Tests\Integration\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

trait ValidateResourceFilters
{
    /**
     * @param array<array{propertyPath: string, message: string}> $violations
     */
    private static function assertResourceHasFilter(KernelBrowser $client, array $violations): void
    {
        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        /** @var array{violations: array<array{propertyPath: string, message: string}>} */
        $payload = json_decode($content, true);

        self::assertSame($violations, array_map(static fn (array $violation): array => [
            'propertyPath' => $violation['propertyPath'],
            'message' => $violation['message'],
        ], $payload['violations']));
    }
}
