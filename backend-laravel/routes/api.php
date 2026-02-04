<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
});

// Protected Routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
        Route::get('/me', [\App\Http\Controllers\AuthController::class, 'me']);
    });

    Route::patch('/users/{id}/toggle-active', [\App\Http\Controllers\UserController::class, 'toggleActive']);
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('services', \App\Http\Controllers\ServiceController::class);
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::resource('orders', \App\Http\Controllers\OrderController::class);
    Route::put('/orders/{id}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus']);
    Route::put('/orders/{id}/payment', [\App\Http\Controllers\OrderController::class, 'updatePayment']);
    Route::get('/orders/{id}/receipt', [\App\Http\Controllers\OrderController::class, 'getReceipt']);

    // Dashboard & Reports
    Route::get('/dashboard/owner', [\App\Http\Controllers\DashboardController::class, 'owner']);
    Route::get('/dashboard/kasir', [\App\Http\Controllers\DashboardController::class, 'kasir']);
    Route::get('/reports/daily', [\App\Http\Controllers\ReportController::class, 'daily']);
    Route::get('/reports/monthly', [\App\Http\Controllers\ReportController::class, 'monthly']);
    Route::get('/reports/yearly', [\App\Http\Controllers\ReportController::class, 'yearly']);

    // Settings
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index']);
    Route::put('/settings', [\App\Http\Controllers\SettingsController::class, 'update']);
});