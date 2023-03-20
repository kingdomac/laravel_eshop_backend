<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPaswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\UserController;

Route::prefix('v1')->group(function () {
    // Authentications
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/forgot-password', [ResetPaswordController::class, 'sendResetLink'])->name('password.email');
    Route::post('/reset-password', [ResetPaswordController::class, 'handleResetPassword'])->name('password.update');

    // Authenticated & verified users
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    });

    // Only Authenticated users
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/email/verification-notification', [VerifyEmailController::class, 'resendEmailVerification'])
            ->name('verification.send');
        Route::get('/user', [UserController::class, 'getAuthUser'])->name('user.auth.show');
        Route::put('/user', [UserController::class, 'updateAuthUser'])->name('user.auth.update');
        Route::put('/change-password', [UserController::class, 'changePassword'])->name('user.auth.change.password');
    });

    // Categories
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'index')->name('categories.list');
        Route::get('/categories-home', 'homeCategories')->name('categories.home');
        Route::get('/categories/{category:slug}/products', 'loadCategory')->name('category.products');
    });

    // Products
    Route::controller(ProductController::class)->group(function () {
        Route::get('/products', 'index')->name('products.list');
        Route::get('/products/{product}/related-products',  'getRelatedProducts')->name('products.related');
        Route::get('/products/{product:slug}', 'show')->name('product.show');
    });

    // Order
    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders/{order:number}', 'show')->name("orders.show");
        Route::post('/orders/checkout', 'purchase')->name("orders.checkout");
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/orders', 'index')->name("orders.list");
            Route::post('/auth/orders/checkout',  'purchase')->name("orders.auth.checkout");
        });
    });
});
