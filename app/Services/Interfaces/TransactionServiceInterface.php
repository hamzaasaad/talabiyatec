<?php

namespace App\Services\Interfaces;
use Closure;

interface TransactionServiceInterface

{
    public function run(Closure $callback);
}