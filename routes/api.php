<?php

declare(strict_types = 1);

use App\Http\Controllers\Auth\Login;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('login', Login::class)->name('login');
    Route::post('purchases')->name('purchases.store');

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/')->name('index');
            Route::get('{id}')->name('show');
        });

        Route::prefix('gateways')->name('gateways.')->group(function () {
            Route::patch('{id}')->name('toggle');
            Route::patch('{id}/priority')->name('priority');
        });

        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/')->name('index');
            Route::get('{id}')->name('show');
            Route::post('/')->name('store');
            Route::put('{id}')->name('update');
            Route::delete('{id}')->name('destroy');
        });

        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/')->name('index');
            Route::get('{id}')->name('show');
            Route::post('{id}/refund')->name('refund');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/')->name('index');
            Route::get('{id}')->name('show');
            Route::post('/')->name('store');
            Route::put('{id}')->name('update');
            Route::delete('{id}')->name('destroy');
        });

    });
});
