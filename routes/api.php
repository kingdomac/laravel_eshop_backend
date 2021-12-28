<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPaswordController;
use App\Http\Controllers\Auth\SocialiteController;
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
        Route::post('/auth/orders/checkout', [OrderController::class, 'purchase'])->name("orders.auth.checkout");
        Route::get('/orders', [OrderController::class, 'index'])->name("orders.list");
    });

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.list');
    Route::get('/categories-home', [CategoryController::class, 'homeCategories'])->name('categories.home');
    Route::get('/categories/{category:slug}/products', [CategoryController::class, 'loadCategory'])->name('category.products');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.list');
    Route::get('/products/{product}/related-products', [ProductController::class, 'getRelatedProducts'])->name('products.related');
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('product.show');

    // Order
    Route::get('/orders/{order:number}', [OrderController::class, 'show'])->name("orders.show");
    Route::post('/orders/checkout', [OrderController::class, 'purchase'])->name("orders.checkouts");
});
