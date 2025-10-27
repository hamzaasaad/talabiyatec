<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\ApiHandler;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
     ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'gzip' => App\Http\Middleware\GzipResponse::class,
        'auth.jwt' => App\Http\Middleware\AuthenticateJwt::class,
        'role.admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,

        ]);
            $middleware->append(App\Http\Middleware\GzipResponse::class);

    })
     ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render([new ApiHandler, '__invoke']);
    })->create();
