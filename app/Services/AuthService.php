<?php

namespace App\Services;

use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\JwtProviderInterface;
use App\Services\Interfaces\TransactionServiceInterface;
use App\Services\Interfaces\ActivityServiceInterface;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\RefreshTokenRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidRefreshTokenException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        protected JwtProviderInterface $jwt,
        protected RefreshTokenRepositoryInterface $refreshRepo,
        protected UserRepositoryInterface $userRepo,
        protected TransactionServiceInterface $tx,
        protected ActivityServiceInterface $activityService

    ) {}

   
    public function login(string $email, string $password, ?string $device, string $ip): array
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new InvalidCredentialsException();
        }

       /**  if (!$user->is_active) {
            throw new InvalidCredentialsException('User account is inactive.');
        }   
            */
$device = $device 
    ?? request()->header('X-Device-Name') 
    ?? request()->userAgent();
    $ip = $ip ?? request()->ip(); 
        $access = $this->jwt->createAccessToken($user);
        $refreshPlain = Str::random(80);
        $hash = hash('sha256', $refreshPlain);
$expires = now()->addDays(config('jwt.refresh_ttl'));

        $this->refreshRepo->create([
            'user_id' => $user->id,
            'token_hash' => $hash,
            'device_name' => $device,
            'ip' => $ip,
            'expires_at' => $expires,
        ]);
$this->activityService->record(
    $user,
    'login',
    ['ip' => $ip, 'device' => $device],
    'auth',
    $user
);



        return [
            'access_token' => $access,
            'refresh_token' => $refreshPlain,
            'expires_in' => env('ACCESS_TOKEN_TTL', 15) * 60,
            'refresh_expires_in' => $expires->timestamp,
            'user' => $user,
        ];
    }

   
    public function refresh(string $refreshTokenPlain, ?string $device, string $ip): array
    {
        $hash = hash('sha256', $refreshTokenPlain);
        $old = $this->refreshRepo->findByHash($hash);

        if (!$old || $old->revoked || $old->isExpired()) {
            throw new InvalidRefreshTokenException();
        }

        return $this->tx->run(function () use ($old, $device, $ip) {
            $user = $old->user;
            $old->revoke();

            $newPlain = Str::random(80);
            $newHash = hash('sha256', $newPlain);
$expires = now()->addDays((int) config('jwt.refresh_ttl', 30));

            $this->refreshRepo->create([
                'user_id' => $user->id,
                'token_hash' => $newHash,
                'device_name' => $device,
                'ip' => $ip,
                'expires_at' => $expires,
            ]);
 $this->activityService->record(
                $user,
                'token_refreshed',
                ['ip' => $ip, 'device' => $device],
                'auth',
                $user
            );
            return [
                'access_token' => $this->jwt->createAccessToken($user),
                'refresh_token' => $newPlain,
                'expires_in' => env('ACCESS_TOKEN_TTL', 15) * 60,
                'refresh_expires_in' => $expires->timestamp,
                'user' => $user,
            ];
        });
    }

     public function logout(string $refreshTokenPlain): void
    {
        $hash = hash('sha256', $refreshTokenPlain);
        $token = $this->refreshRepo->findByHash($hash);

        if ($token && !$token->revoked) {
            $token->revoke();

            $this->activityService->record(
                $token->user,
                'logout',
                ['device' => $token->device_name, 'ip' => $token->ip],
                'auth',
                $token->user
            );
        }
    }
  public function getActiveSessions(int $userId): array
{
    $tokens = $this->refreshRepo->getActiveForUser($userId);

    return $tokens->map(function ($token) {
        return [
            'id' => $token->id,
            'device_name' => $token->device_name,
            'ip' => $token->ip,
            'created_at' => $token->created_at->toDateTimeString(),
            'expires_at' => $token->expires_at->toDateTimeString(),
        ];
    })->toArray();
}

  public function logoutAll(int $userId): void
    {
        $this->refreshRepo->revokeAllForUser($userId);
        $user = $this->userRepo->findById($userId);

        if ($user) {
            $this->activityService->record(
                $user,
                'logout_all',
                ['action' => 'revoked_all_sessions'],
                'auth',
                $user
            );
        }
    }
}
