<?php

namespace App\Tests\Integration\Traits;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticatedClientTrait
{
    private static function createAuthenticatedClient(string $email): KernelBrowser
    {
        $client = static::createClient();

        /** @var UserRepository */
        $repository = self::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['email' => $email]);
        if (null === $user) {
            self::fail(\sprintf('User with email "%s" not found', $email));
        }

        $client->loginUser($user);

        $jwtManager = self::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);

        $client->setServerParameter('HTTP_Authorization', \sprintf('Bearer %s', $token));

        return $client;
    }
}
