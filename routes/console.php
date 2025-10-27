<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('refresh-tokens:cleanup')
             ->dailyAt('02:00')
             ->withoutOverlapping()
             ->runInBackground()
             ->appendOutputTo(storage_path('logs/scheduler.log'));