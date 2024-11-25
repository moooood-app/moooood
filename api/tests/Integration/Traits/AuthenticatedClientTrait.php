<?php

namespace App\Tests\Integration\Traits;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticatedClientTrait
{
    private static function createAuthenticatedClient(string $email): KernelBrowser
    {
        $client = static::createClient();

        $user = self::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);

        if (null === $user) {
            throw new \InvalidArgumentException(\sprintf('User with email "%s" not found', $email));
        }

        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);

        $client->setServerParameter('HTTP_Authorization', \sprintf('Bearer %s', $token));

        return $client;
    }
}
