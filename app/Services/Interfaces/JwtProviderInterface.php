<?php

namespace App\Services\Interfaces;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Token\Plain;

use App\Models\User;

interface JwtProviderInterface
{
    public function createAccessToken(User $user, array $claims = []): string;
            public function decode(string $token): array;
    public function validate(Plain $token): bool;
    public function parse(string $token): Plain;

}
