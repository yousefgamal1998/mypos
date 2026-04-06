<?php

use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\Client\OrderController;
use App\Http\Controllers\Dashboard\CustomerController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::get('/', function () {
    $locale = session('locale', app()->getLocale());

    return redirect(LaravelLocalization::getLocalizedURL($locale, url('/')));
});

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['web', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
], function () {
    Route::get('/', function () {
        return auth()->check()
            ? redirect()->route('dashboard.index')
            : redirect()->route('login');
    });

    Route::prefix('dashboard')->name('dashboard.')->middleware('auth')->group(function () {
        Route::controller(DashboardController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('index', 'index')->name('home');
        });

        Route::get('customers/{customer}/orders/create', [OrderController::class, 'createForCustomer'])
            ->name('customers.orders.create');
        Route::resource('orders', OrderController::class);
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('customers', CustomerController::class);
        Route::resource('users', UserController::class);
    });

    require __DIR__.'/auth.php';

});