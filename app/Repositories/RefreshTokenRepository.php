<?php

namespace App\Repositories;

use App\Repositories\Interfaces\RefreshTokenRepositoryInterface;
use App\Models\RefreshToken;
use Illuminate\Support\Collection;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function create(array $data): RefreshToken
    {
        return RefreshToken::create($data);
    }

    public function findByHash(string $hash): ?RefreshToken
    {
        return RefreshToken::where('token_hash', $hash)->first();
    }

    public function revokeByHash(string $hash): int
    {
        return RefreshToken::where('token_hash', $hash)->update(['revoked' => true]);
    }

    public function revokeAllForUser(int $userId): int
    {
        return RefreshToken::where('user_id', $userId)->update(['revoked' => true]);
    }

    public function deleteExpired(): int
    {
        return RefreshToken::where('expires_at', '<', now())->delete();
    }

    public function getActiveForUser(int $userId): Collection
    {
        return RefreshToken::where('user_id', $userId)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->get();
    }
    public function deleteOldRevoked(\Carbon\Carbon $date): int
{
    return RefreshToken::where('revoked', true)
        ->where('updated_at', '<', $date)
        ->delete();
}

}
