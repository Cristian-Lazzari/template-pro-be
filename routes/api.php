<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\DateController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\ReservationController;



Route::get('setting',           [SettingController::class, 'index'])->name('api.setting.index');


Route::get('menus',             [ProductController::class, 'menuFissi'])->name('api.products.menuFissi');
Route::get('products',          [ProductController::class, 'index'])->name('api.products.index');
Route::get('promoHome',         [ProductController::class, 'promoHome'])->name('api.promoHome.index');

Route::get('categories',        [CategoryController::class, 'index'])->name('api.categories.index');
Route::get('getIngredient',     [IngredientController::class, 'getIngredient'])->name('api.ingredient.getIngredient');

Route::get('post',              [PostController::class, 'index'])->name('api.post.index');
Route::get('postHome',          [PostController::class, 'postHome'])->name('api.postHome.index');

Route::get('dates',             [DateController::class, 'index'])->name('api.dates.index');
Route::get('getDays',           [DateController::class, 'getDays'])->name('api.dates.getDays');

Route::get('client_default',    [SettingController::class, 'client_default'])->name('api.client_default'); // annullamento tramite mail
Route::post('reservations',     [ReservationController::class, 'store'])->name('api.reservations.store');
Route::post('orders',           [OrderController::class, 'store'])->name('api.orders.store');

Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('send-otp', [CustomerAuthController::class, 'sendOtp'])->name('send_otp');
    Route::post('verify-otp', [CustomerAuthController::class, 'verifyOtp'])->name('verify_otp');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [CustomerAuthController::class, 'me'])->name('me');
        Route::get('history', [CustomerAuthController::class, 'history'])->name('history');
        Route::post('logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });
});


Route::get('/checkout',         [PaymentController::class, 'checkout'])->name('api.payment.checkout');
