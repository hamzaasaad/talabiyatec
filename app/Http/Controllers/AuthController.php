<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\LogoutAllRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Services\Interfaces\AuthServiceInterface;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthServiceInterface $auth) {}

    public function login(LoginRequest $request)
    {
        $result = $this->auth->login(
            $request->input('email'),
            $request->input('password'),
            $request->input('device_name'),
            $request->ip()
        );

        return $this->successResponse(
            new AuthResource($result),
            'Login successful'
        );
    }

    public function refresh(RefreshRequest $request)
    {
        $result = $this->auth->refresh(
            $request->input('refresh_token'),
            $request->userAgent(),
            $request->ip()
        );

        return $this->successResponse(
            new AuthResource($result),
            'Token refreshed successfully'
        );
    }

    public function logout(LogoutRequest $request)
    {
        $this->auth->logout($request->input('refresh_token'));
        return $this->successResponse(null, 'Logged out successfully');
    }
    
public function sessions()
{
    $user = auth()->user();

    $sessions = $this->auth->getActiveSessions($user->id);

    return $this->successResponse($sessions, 'Active sessions retrieved successfully');
}

    public function logoutAll(LogoutAllRequest $request)
    {
        $this->auth->logoutAll(auth()->id());
        return $this->successResponse(null, 'Logged out from all devices successfully');
    }
}
