<?php

namespace App\Repositories;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\User;
class UserRepository implements UserRepositoryInterface {
    
    public function findByEmail(string $email): ?User {
        return User::where('email', $email)->first();
    }
      public function findById(int $id): ?User
    {
        return User::find($id);
    }
}
