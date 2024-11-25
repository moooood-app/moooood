<?php

namespace App\Tests\Integration\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

trait ValidationErrorsTrait
{
    /**
     * @param array<array{propertyPath: string, message: string}> $violations
     */
    private static function assertValidationErrors(KernelBrowser $client, array $violations): void
    {
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

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
