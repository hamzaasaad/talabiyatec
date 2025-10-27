<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Interfaces\JwtProviderInterface;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthenticateJwt
{
    use ApiResponse;

    public function __construct(private JwtProviderInterface $jwt) {}

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->errorResponse('Unauthorized - Missing Bearer Token', 401);
        }

        try {
            $claims = $this->jwt->decode($token);
        } catch (Exception $e) {
            Log::warning('Invalid JWT detected', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'error' => $e->getMessage(),
            ]);
            return $this->errorResponse('Invalid or expired token', 401);
        }

        $user = User::find($claims['sub'] ?? null);

        if (!$user) {
            return $this->errorResponse('User not found or inactive', 401);
        }

        auth()->setUser($user);

        return $next($request);
    }
}
