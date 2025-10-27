<?php

namespace App\Providers;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
         RateLimiter::for('global', function (Request $request) {
            $userId = $request->user()?->id;
            $ip = $request->ip();

            return Limit::perMinute(100)
                ->by($userId ?: $ip);
               
        });

        RateLimiter::for('login', function (Request $request) {
            $key = $request->ip() . '|' . $request->input('email');
            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('guest', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
