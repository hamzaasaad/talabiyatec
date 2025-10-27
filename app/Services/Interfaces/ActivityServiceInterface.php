<?php

namespace App\Services\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ActivityServiceInterface
{
    public function record(
        User $user,
        string $event,
        array $properties = [],
        string $logName = 'default',
        ?Model $subject = null,
        ?string $batchUuid = null
    ): void;

    public function getFiltered(?string $logName, ?string $event, int $perPage = 10): LengthAwarePaginator;
}
