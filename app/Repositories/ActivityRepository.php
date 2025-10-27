<?php

namespace App\Repositories;

use App\Repositories\Interfaces\ActivityRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Facades\Activity as ActivityFacade;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityRepository implements ActivityRepositoryInterface
{
    public function log(
        User $user,
        string $event,
        array $properties = [],
        string $logName = 'default',
        ?Model $subject = null,
        ?string $batchUuid = null
    ): void {
        // إضافة batchUuid داخل الخصائص (properties)
        if ($batchUuid) {
            $properties['batch_uuid'] = $batchUuid;
        }

        ActivityFacade::causedBy($user)
            ->performedOn($subject)
            ->withProperties($properties)
            ->useLog($logName)
            ->event($event)
            ->log("User {$user->email} performed {$event}");
    }

    public function filterPaginated(?string $logName, ?string $event, int $perPage = 10):LengthAwarePaginator
    {
        $query = Activity::query();

        if ($logName) {
            $query->where('log_name', $logName);
        }

        if ($event) {
            $query->where('event', $event);
        }

        return $query->latest('created_at')->paginate($perPage);
    }
}
