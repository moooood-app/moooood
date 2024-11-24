<?php

namespace App\Tests\Integration\Authentication;

use App\DataFixtures\UserFixtures;
use App\Entity\UserRefreshToken;
use App\Repository\UserRepository;
use App\Tests\Integration\Traits\ValidateJsonSchemaTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(UserRefreshToken::class)]
#[CoversClass(UserRepository::class)]
final class RefreshTokenTest extends WebTestCase
{
    use ValidateJsonSchemaTrait;

    public function testSuccessfulRefreshToken(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_POST, '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], (string) json_encode([
            'email' => UserFixtures::FIRST_USER,
            'password' => UserFixtures::PASSWORD,
        ]));

        $this->assertResponseIsSuccessful();

        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        /**
         * @var object{
         *   token: string,
         *   refresh_token: string,
         * } $data
         */
        $data = json_decode($content);

        $this->assertJsonSchemaIsValid($data, 'authentication/token.json');

        $client->request(Request::METHOD_POST, '/api/token/refresh', [
            'refresh_token' => $data->refresh_token,
        ]);

        $this->assertResponseIsSuccessful();

        /** @var non-empty-string */
        $content = $client->getResponse()->getContent();

        /**
         * @var object{
         *   token: string,
         *   refresh_token: string,
         * } $data
         */
        $data = json_decode($content);

        $this->assertJsonSchemaIsValid($data, 'authentication/token.json');
    }

    public function testNonSuccessfulRefresh(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_POST, '/api/token/refresh', [
            'refresh_token' => 'invalid-refresh-token',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}