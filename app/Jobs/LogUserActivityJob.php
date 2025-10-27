<?php

namespace App\Jobs;

use App\Models\User;
use App\Repositories\Interfaces\ActivityRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogUserActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected User $user,
        protected string $event,
        protected array $properties = [],
        protected string $logName = 'default',
        protected ?Model $subject = null,
        protected ?string $batchUuid = null
    ) {}

    public function handle(ActivityRepositoryInterface $activityRepo): void
    {
        $activityRepo->log(
            $this->user,
            $this->event,
            $this->properties,
            $this->logName,
            $this->subject,
            $this->batchUuid
        );
    }
}
