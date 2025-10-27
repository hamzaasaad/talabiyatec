<?php
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityController;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware(['throttle:guest'])->get('/test', function () {
    return response()->json(['ok']);
});

Route::middleware(['auth.jwt','throttle:global', 'role.admin'])->group(function () {
    Route::get('v1/activities', [ActivityController::class, 'index']);
});
Route::middleware(['auth.jwt', 'role.admin'])->group(function () {
    // Route::get('log-viewer', \Rap2hpoutre\LaravelLogViewer\LogViewerController::class . '@index');
});

Route::middleware(['throttle:login'])->post('v1/auth/login', [AuthController::class, 'login']);
Route::post('v1/auth/refresh', [AuthController::class, 'refresh']);

Route::middleware(['auth.jwt'])->group(function () {
    Route::post('v1/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn() => auth()->user());
    Route::get('v1/auth/sessions', [AuthController::class, 'sessions']);
    Route::post('v1/auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/debug', function() {
    return response()->json([
        'message' => 'Debug test',
        'status' => 'working'
    ]);
});

});