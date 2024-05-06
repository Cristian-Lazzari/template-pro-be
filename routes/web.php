<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DayController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\DateController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SlotController;
use App\Http\Controllers\Admin\TimeController;
use App\Http\Controllers\Admin\MonthController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\AddressController;
use App\Http\Controllers\Admin\HashtagController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\admin\NotificationController;
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
