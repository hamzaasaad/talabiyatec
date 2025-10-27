<?php

namespace App\Services;
use App\Services\Interfaces\TransactionServiceInterface;
use Closure;
use Illuminate\Support\Facades\DB;

class DatabaseTransactionService implements TransactionServiceInterface

{
    public function run(Closure $callback)
    {
        return DB::transaction($callback);
    }
}
