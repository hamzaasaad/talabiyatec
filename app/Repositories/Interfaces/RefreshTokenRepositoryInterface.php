<?php

namespace App\Repositories\Interfaces;

use App\Models\RefreshToken;
use Illuminate\Support\Collection;

interface RefreshTokenRepositoryInterface
{
    public function create(array $data): RefreshToken;
    public function findByHash(string $hash): ?RefreshToken;
    public function revokeByHash(string $hash): int;
    public function revokeAllForUser(int $userId): int;
    public function deleteExpired(): int;
    public function getActiveForUser(int $userId): Collection;
    public function deleteOldRevoked(\Carbon\Carbon $date): int;

}
