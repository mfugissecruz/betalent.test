<?php

declare(strict_types = 1);

use App\Enum\UserRole;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('login', Login::class)->name('login');
    Route::post('purchases')->name('purchases.store');

    Route::middleware('auth:sanctum')->group(function () {

        // Accessible by all authenticated roles
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/')->name('index');
            Route::get('{id}')->name('show');
        });

        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/')->name('index');
            Route::get('{id}')->name('show');
        });

        // ADMIN only
        Route::middleware('role:' . UserRole::ADMIN->value)->group(function () {
            Route::prefix('gateways')->name('gateway.')->group(function () {
                Route::patch('{id}/activate')->name('activate');
                Route::patch('{id}/deactivate')->name('deactivate');
                Route::patch('{id}/priority')->name('priority');
            });
        });

        // ADMIN, MANAGER
        Route::apiResource('users', UserController::class)
            ->names('users')
            ->middleware('role:' . UserRole::ADMIN->value . ',' . UserRole::MANAGER->value);

        // ADMIN, MANAGER, FINANCE
        Route::middleware('role:' . UserRole::ADMIN->value . ',' . UserRole::MANAGER->value . ',' . UserRole::FINANCE->value)->group(function () {
            Route::prefix('products')->name('products.')->group(function () {
                Route::get('/')->name('index');
                Route::get('{id}')->name('show');
                Route::post('/')->name('store');
                Route::put('{id}')->name('update');
                Route::delete('{id}')->name('destroy');
            });

            Route::post('transactions/{id}/refund')->name('transactions.refund');
        });

    });
});
