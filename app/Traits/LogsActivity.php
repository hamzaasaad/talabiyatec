<?php

namespace App\Traits;

use App\Jobs\LogUserActivityJob;

trait LogsActivity
{
    public function logActivity(string $event, array $properties = []): void
    {
        dispatch(new LogUserActivityJob($this, $event, $properties));
    }
}
