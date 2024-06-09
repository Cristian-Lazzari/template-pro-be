<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DateController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Guests\PageController as GuestsPageController;


Route::get('/', function () {
    return view('guests/home');
});

Route::middleware(['auth', 'verified'])
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {

        Route::get('/',        [AdminPageController::class, 'dashboard'])->name('dashboard');
        Route::get('/setting', [AdminPageController::class, 'setting'])->name('setting');
   
        // Rotte product
        
        Route::get('products/archived', [ProductController::class, 'archived'])->name('products.archived');
        Route::post('products/filter',  [ProductController::class, 'filter'])->name('products.filter');
        Route::post('products/status',  [ProductController::class, 'status'])->name('products.status');
        
        // Rotte post
        
        Route::get('posts/archived', [PostController::class, 'archived'])->name('posts.archived');
        Route::post('posts/filter',  [PostController::class, 'filter'])->name('posts.filter');
        Route::post('posts/status',  [PostController::class, 'status'])->name('posts.status');
        
        // Rotte Date 
        
        Route::get('dates/showDay',    [DateController::class, 'showDay'])->name('dates.showDay');
        Route::post('dates/status',    [DateController::class, 'status'])->name('dates.status');
        Route::post('/dates/generate', [DateController::class, 'generate'])->name('dates.generate');
        
        Route::post('orders/filter',       [OrderController::class, 'filter'])->name('orders.filter');
        Route::post('reservations/filter', [ReservationController::class, 'filter'])->name('reservations.filter');
        //resource
        Route::resource('dates',         DateController::class);
        Route::resource('orders',        OrderController::class);
        Route::resource('products',      ProductController::class);
        Route::resource('posts',         PostController::class);
        Route::resource('reservations',  ReservationController::class);
        Route::resource('ingredients',   IngredientController::class);
        Route::resource('categories',    CategoryController::class);
    });

Route::middleware('auth')
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });


require __DIR__ . '/auth.php';
