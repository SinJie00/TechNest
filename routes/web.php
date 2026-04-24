<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/shop', function () {
    return view('shop');
})->name('shop');

Route::get('/brands', function () {
    $brands = \App\Models\Brand::all();
    return view('brands', compact('brands'));
})->name('brands');

Route::get('/product/{id}', function ($id) {
    return view('product', ['id' => $id]);
})->name('product.show');

Route::get('/cart', function () {
    return view('cart');
})->name('cart');

Route::get('/checkout', function () {
    return view('checkout');
})->name('checkout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/orders', function () {
        return view('orders');
    })->name('orders.index');
});

Route::middleware(['auth', \App\Http\Middleware\IsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    Route::post('products/import', [\App\Http\Controllers\Admin\ProductController::class, 'importStore'])->name('products.import');
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
        Route::resource('subcategories', App\Http\Controllers\Admin\SubcategoryController::class);
        Route::resource('brands', App\Http\Controllers\Admin\BrandController::class);
        Route::get('orders', [App\Http\Controllers\OrderController::class, 'adminView'])->name('orders');
});

require __DIR__.'/auth.php';
