<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\DateController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SlotController;
use App\Http\Controllers\Api\TimeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ReservationController;

Route::get('products',          [ProductController::class, 'index'])->name('api.products.index');
Route::get('categories',        [CategoryController::class, 'index'])->name('api.categories.index');
Route::get('setting',           [SettingController::class, 'index'])->name('api.setting.index');
Route::get('post',              [PostController::class, 'index'])->name('api.post.index');
Route::get('ingredient',        [IngredientController::class, 'index'])->name('api.ingredient.index');
Route::post('reservations',     [ReservationController::class, 'store'])->name('api.reservations.store');
Route::post('orders',           [OrderController::class, 'store'])->name('api.orders.store');
Route::get('dates',             [DateController::class, 'index'])->name('api.dates.index');
//wcf =  where it come from e puo essere 0 (se viene da p-tavoli), 1 (se viene da p-asporto), 2 (se viene da p-domicilio),
