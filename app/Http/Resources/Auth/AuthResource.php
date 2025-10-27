<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'access_token' => $this['access_token'],
            'refresh_token' => $this['refresh_token'],
            'expires_in' => $this['expires_in'],
            'refresh_expires_in' => $this['refresh_expires_in'],
            'user' => [
                'id' => $this['user']->id,
                'name' => $this['user']->name,
                'email' => $this['user']->email,
                'roles' => $this['user']->getRoleNames(),
            ],
        ];
    }
}
