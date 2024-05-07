<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DateController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Guests\PageController as GuestsPageController;


Route::get('/', function () {
    return view('guests/home');
});

Route::middleware(['auth', 'verified'])
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {

        Route::get('/',                                      [AdminPageController::class, 'dashboard'])->name('dashboard');
        Route::get('/setting',                               [AdminPageController::class, 'setting'])->name('setting');
   
        // Rotte Resource
        Route::resource('dates',        DateController::class);
        Route::resource('products',     ProductController::class);
        Route::resource('ingredients',  IngredientController::class);
        Route::resource('categories',   CategoryController::class);
        
        Route::post('products/special', [ProductController::class, 'special']);

        // Rotte Date 

        Route::post('/dates/updatestatus/v}',               [DateController::class, 'updatestatus'])->name('dates.updatestatus');      
        Route::post('/dates/updateMax}',                    [DateController::class, 'updateMax'])->name('dates.updateMax');
        Route::post('/dates/runSeeder',                     [DateController::class, 'runSeeder'])->name('dates.runSeeder');
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
