<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\UserJWT;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class APIAuthController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Security $security,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    #[Route('/auth', name: 'app_api_auth')]
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new UnauthorizedHttpException('User must be authenticated to generate a JWT token');
        }

        $token = new UserJWT($this->jwtManager->create($user));

        return new JsonResponse($this->normalizer->normalize($token));
    }
}
