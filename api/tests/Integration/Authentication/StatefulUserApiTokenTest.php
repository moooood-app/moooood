<?php

namespace App\Tests\Integration\JWT;

use App\Controller\TokenController;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(TokenController::class)]
#[UsesClass(UserRepository::class)]
final class StatefulUserApiTokenTest extends WebTestCase
{
    public function testAuthenticatedUserCanRetrieveJWT(): void
    {
        $client = self::createClient();
        $userRepository = self::getContainer()->get(UserRepository::class);

        /** @var User */
        $testUser = $userRepository->findOneByEmail(UserFixtures::FIRST_USER);

        $client->loginUser($testUser);

        $client->request('GET', '/auth');
        $this->assertResponseHasHeader('Content-Type', 'application/json');
        $this->assertResponseStatusCodeSame(201);

        /** @var string */
        $content = $client->getResponse()->getContent();

        /** @var array<string, string> $data */
        $data = json_decode($content, true);

        self::assertArrayHasKey('token', $data);
    }

    public function testNonAuthenticatedUserCannotRetrieveJWT(): void
    {
        $client = self::createClient();

        $client->request('GET', '/auth');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $this->assertResponseHasHeader('Content-Type', 'application/json');

        /** @var string */
        $content = $client->getResponse()->getContent();

        /** @var array<string, string> $data */
        $data = json_decode($content, true);

        self::assertSame(['message' => 'User must be authenticated to generate a JWT token'], $data);
    }
}
