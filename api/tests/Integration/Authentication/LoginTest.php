<?php

namespace App\Tests\Integration\Authentication;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Integration\Traits\ValidateJsonSchemaTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
final class LoginTest extends WebTestCase
{
    use ValidateJsonSchemaTrait;

    public function testSuccessfulLogin(): void
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
         *   refreshToken: string,
         * } $data
         */
        $data = json_decode($content);

        $this->assertJsonSchemaIsValid($data, 'authentication/token.json');
    }

    public function testNonSuccessfulLogin(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_POST, '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], (string) json_encode([
            'email' => UserFixtures::FIRST_USER,
            'password' => 'wrong-password',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
