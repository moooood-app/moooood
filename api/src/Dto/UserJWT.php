<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class UserJWT
{
    public function __construct(private readonly string $token)
    {
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
