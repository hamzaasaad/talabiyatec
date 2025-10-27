<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Jobs\LogUserActivityJob;
use App\Services\Interfaces\ActivityServiceInterface;
use App\Repositories\Interfaces\ActivityRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityService implements ActivityServiceInterface
{
    public function __construct(
        protected ActivityRepositoryInterface $activityRepo
    ) {}

    public function record(
        User $user,
        string $event,
        array $properties = [],
        string $logName = 'default',
        ?Model $subject = null,
        ?string $batchUuid = null
    ): void {
        dispatch(new LogUserActivityJob(
            $user,
            $event,
            $properties,
            $logName,
            $subject,
            $batchUuid ?? Str::uuid()->toString()
        ));
    }

    public function getFiltered(?string $logName, ?string $event, int $perPage = 10): LengthAwarePaginator
    {
        return $this->activityRepo->filterPaginated($logName, $event, $perPage);
    }
}
