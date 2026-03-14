<?php

declare(strict_types = 1);

use App\Enum\UserRole;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\{GatewayController, PurchaseController, RefundController};
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('login', Login::class)->name('login');
    Route::post('purchase', PurchaseController::class)->name('purchase.store');

    Route::middleware('auth:sanctum')->group(function () {

        Route::apiResource('clients', ClientController::class)
            ->only(['index', 'show'])
            ->names('clients');

        Route::apiResource('transactions', TransactionController::class)
            ->only(['index', 'show'])
            ->names('transactions');

        Route::apiResource('users', UserController::class)
            ->names('users')
            ->middleware(UserRole::allows(UserRole::ADMIN, UserRole::MANAGER));

        Route::middleware(UserRole::allows(UserRole::ADMIN))->group(function () {
            Route::prefix('gateways')->name('gateway.')->group(function () {
                Route::patch('{gateway}/activate', [GatewayController::class, 'activate'])->name('activate');
                Route::patch('{gateway}/deactivate', [GatewayController::class, 'deactivate'])->name('deactivate');
                Route::patch('{gateway}/priority', [GatewayController::class, 'updatePriority'])->name('priority');
            });
        });

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{id}', [ProductController::class, 'show'])->name('products.show');

        Route::middleware(UserRole::allows(UserRole::ADMIN, UserRole::MANAGER, UserRole::FINANCE))->group(function () {
            Route::apiResource('products', ProductController::class)->names('products')->except(['index', 'show']);
        });

        Route::middleware(UserRole::allows(UserRole::ADMIN, UserRole::FINANCE))->group(function () {
            Route::post('transactions/{transaction}/refund', RefundController::class)->name('transactions.refund');
        });
    });
});
