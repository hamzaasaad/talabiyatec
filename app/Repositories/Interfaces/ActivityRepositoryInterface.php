<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ActivityRepositoryInterface
{
    public function log(
        User $user,
        string $event,
        array $properties = [],
        string $logName = 'default',
        ?Model $subject = null,
        ?string $batchUuid = null
    ): void;

    public function filterPaginated(?string $logName, ?string $event, int $perPage = 10): LengthAwarePaginator;
}
