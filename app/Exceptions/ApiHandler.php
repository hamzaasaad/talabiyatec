<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidRefreshTokenException;

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use App\Traits\ApiResponse;

class ApiHandler
{
    use ApiResponse;

    public function __invoke(Throwable $e, Request $request)
    {
        if (!($request->is('api/*') || $request->expectsJson())) {
            return null;
        }

        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
        ];

        $level = match (true) {
            $e instanceof ValidationException => 'info', 
            $e instanceof ThrottleRequestsException => 'warning', 
            $e instanceof AuthenticationException => 'warning', 
            $e instanceof AuthorizationException => 'warning', 
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => 'error', 
              $e instanceof InvalidRefreshTokenException => 'error', 
            $e instanceof MethodNotAllowedHttpException => 'error',
            default => 'error', 
        };

      
        Log::channel('api')->{$level}("[$level] {$e->getMessage()}", $context);

        
        return match (true) {
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => $this->errorResponse('Resource not found', 404),

            $e instanceof MethodNotAllowedHttpException => $this->errorResponse('Method not allowed', 405),

            $e instanceof AuthenticationException => $this->errorResponse('Unauthenticated', 401),
$e instanceof InactiveUserException => $this->errorResponse('User account is inactive', 403),

            $e instanceof AuthorizationException => $this->errorResponse('Forbidden', 403),
$e instanceof InvalidRefreshTokenException => $this->errorResponse('Invalid or expired refresh token', 401),
$e instanceof ThrottleRequestsException => $this->errorResponse(
    'Too many requests',
    429,
    [
        'limit' => $e->getHeaders()['X-RateLimit-Limit'] ?? null,
        'remaining' => $e->getHeaders()['X-RateLimit-Remaining'] ?? null,
        'retry_after_seconds' => $e->getHeaders()['Retry-After'] ?? null,
    ]
),
    $e instanceof InvalidCredentialsException => $this->errorResponse($e->getMessage(), 401),

            $e instanceof ValidationException => $this->errorResponse('Validation failed', 422, $e->errors()),

            default => $this->errorResponse('Server error', 500),
        };
    }
}
