<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TokenController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Security $security,
    ) {
    }

    #[Route('/auth', name: 'app_api_auth')]
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'User must be authenticated to generate a JWT token'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'token' => $this->jwtManager->create($user),
        ], Response::HTTP_CREATED);
    }
}
