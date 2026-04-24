<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/all', [CategoryController::class, 'all']);
Route::get('/products/{id}/variants', [VariantController::class, 'index']);
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);

// Cart (Using auth:sanctum to support both SPA token and session cookies if configured)
// For simple blade apps without SPA config, we might need middleware('web') or ensure sanctum stateful.
// To be safe for this hybrid, we can use the 'web' middleware group or just ensure the frontend sends credentials.
// Cart
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'add']);
Route::post('/cart/remove', [CartController::class, 'remove']);
Route::post('/cart/update', [CartController::class, 'update']);
Route::post('/checkout', [OrderController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    
    // Admin
    Route::middleware(\App\Http\Middleware\IsAdmin::class)->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/orders', [OrderController::class, 'adminIndex']);
        Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        
        // CRUD
        Route::resource('categories', CategoryController::class)->except(['index', 'show']);
        // Route::resource('products', ProductController::class)->except(['index', 'show']);
    });
});
