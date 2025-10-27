<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class EnsureUserIsAdmin
{
    use ApiResponse;

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        if (!$user->hasRole('مدير النظام')) {
            return $this->errorResponse('Forbidden - Admins only', 403);
        }

        return $next($request);
    }
}
