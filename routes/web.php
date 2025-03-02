<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DateController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Webhooks\WaController;
use App\Http\Controllers\Admin\MailerController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Guests\PageController as GuestsPageController;


Route::get('/', function () {
    return view('guests/home');
});
Route::get('/doc', function () {
    return view('guests/documentazione');
});
Route::get('/delete_succes', function () {
    return view('guests/documentazione');
});


Route::middleware(['auth', 'verified'])
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {

        Route::get('/',           [AdminPageController::class, 'dashboard'])->name('dashboard');
   
        Route::get('/statistics', [AdminPageController::class, 'statistics'])->name('statistics');

        Route::get('/mailer/index',         [MailerController::class, 'mailer'])->name('mailer.index');
        Route::get('/mailer/send_mail',     [MailerController::class, 'send_mail'])->name('mailer.send_mail');
        
        Route::post('/mailer/send_m',       [MailerController::class, 'send_m'])->name('mailer.send_m');
        Route::post('/mailer/extra_list',   [MailerController::class, 'extra_list'])->name('mailer.extra_list');
        
        Route::get('/mailer/create_model',  [MailerController::class, 'create_model'])->name('mailer.create_model');
        Route::post('/mailer/create_m',     [MailerController::class, 'create_m'])->name('mailer.create_m');

      Route::get('/mailer/edit_model/{id}', [MailerController::class, 'edit_model'])->name('mailer.edit_model');

        Route::post('/mailer/update_model', [MailerController::class, 'update_model'])->name('mailer.update_model');
        Route::delete('/models/{id}',       [MailerController::class, 'delete'])->name('models.delete');

        // Rotte setting

        Route::post('settings/numbers',  [SettingController::class, 'numbers'])->name('settings.numbers');
        Route::post('settings/updateAll',  [SettingController::class, 'updateAll'])->name('settings.updateAll');
        Route::post('settings/updateAree', [SettingController::class, 'updateAree'])->name('settings.updateAree');

        Route::post('categories/neworder', [CategoryController::class, 'neworder'])->name('categories.neworder');
        Route::post('categories/new_order_products', [CategoryController::class, 'new_order_products'])->name('categories.new_order_products');

        // Rotte product
        
        Route::get('products/archived', [ProductController::class, 'archived'])->name('products.archived');
        Route::post('products/filter',  [ProductController::class, 'filter'])->name('products.filter');
        Route::post('products/status',  [ProductController::class, 'status'])->name('products.status');
        
        // Rotte order res
        Route::post('orders/status',       [OrderController::class, 'status'])->name('orders.status');
        Route::post('reservations/status', [ReservationController::class, 'status'])->name('reservations.status');
        
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
        Route::resource('settings',      SettingController::class);
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

Route::post('/webhook/stripe', [StripeWebhookController::class, 'handleStripeWebhook']);

Route::get('/notifica',        [AdminPageController::class, 'sendNotification']);



Route::post('/webhook/wa', [WaController::class, 'handle']);
//Route::get('/webhook/wa', [WaController::class, 'verify']); 


