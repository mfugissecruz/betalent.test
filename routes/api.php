<?php

declare(strict_types = 1);

use App\Enum\UserRole;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('login', Login::class)->name('login');
    Route::post('purchases')->name('purchases.store');

    Route::middleware('auth:sanctum')->group(function () {

        Route::apiResource('clients', UserController::class)
            ->only(['index', 'show'])
            ->names('clients.');

        Route::apiResource('transactions', UserController::class)
            ->only(['index', 'show'])
            ->names('transactions.');

        Route::apiResource('users', UserController::class)
            ->names('users.')
            ->middleware(UserRole::allows(UserRole::ADMIN, UserRole::MANAGER));

        Route::middleware(UserRole::allows(UserRole::ADMIN))->group(function () {
            Route::prefix('gateways')->name('gateway.')->group(function () {
                Route::patch('{id}/activate')->name('activate');
                Route::patch('{id}/deactivate')->name('deactivate');
                Route::patch('{id}/priority')->name('priority');
            });
        });

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{id}', [ProductController::class, 'show'])->name('products.show');

        Route::middleware(UserRole::allows(UserRole::ADMIN, UserRole::MANAGER, UserRole::FINANCE))->group(function () {
            Route::apiResource('products', ProductController::class)->names('products.')->except(['index', 'show']);
            Route::post('transactions/{id}/refund')->name('transactions.refund');
        });
    });
});
