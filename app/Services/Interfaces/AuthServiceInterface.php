<?php

namespace App\Services\Interfaces;

interface AuthServiceInterface
{
    public function login(string $phone, string $password, ?string $device, string $ip): array;
    public function refresh(string $refreshTokenPlain, ?string $device, string $ip): array;
    public function logout(string $refreshTokenPlain): void;
    public function logoutAll(int $userId): void;
      public function getActiveSessions(int $userId): array;
}
