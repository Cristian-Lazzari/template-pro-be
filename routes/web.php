<?php

use App\Http\Controllers\Admin\AllergenController;
use App\Http\Controllers\Admin\AutomationController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DateController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\MailerController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Guests\PageController as GuestsPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Support\Facades\Route;


Route::get('/', [GuestsPageController::class, 'home'])->name('guest.home');
Route::get('/documentazione', [GuestsPageController::class, 'documentation'])->name('guest.documentation');
Route::get('/documentazione/{page}', [GuestsPageController::class, 'documentationTopic'])->name('guest.documentation.page');
Route::get('/doc', [GuestsPageController::class, 'documentation']);
Route::get('/doc/{page}', [GuestsPageController::class, 'documentationTopic']);
Route::get('/aggiornamenti', [GuestsPageController::class, 'updates'])->name('guest.updates');
Route::get('/delete_succes', function () {
    return view('guests.delete_success');
});


Route::middleware(['auth', 'verified'])
    ->name('admin.')
    ->prefix('admin')
    ->group(function () {

        Route::get('/',           [AdminPageController::class, 'dashboard'])->name('dashboard');
   
        Route::get('/statistics', [AdminPageController::class, 'statistics'])->name('statistics');
        Route::get('/marketing', [AdminPageController::class, 'marketing'])->name('marketing');
        Route::get('/customers',  [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/guest/{email}', [CustomerController::class, 'showGuest'])->where('email', '.*')->name('customers.show_guest');

        Route::get('/customers/mail-models', [MailerController::class, 'indexModels'])->name('customers.mail_models.index');
        Route::get('/customers/mail-models/create', [MailerController::class, 'createModel'])->name('customers.mail_models.create');
        Route::post('/customers/mail-models', [MailerController::class, 'storeModel'])->name('customers.mail_models.store');
        Route::get('/customers/mail-models/{id}/edit', [MailerController::class, 'editModel'])->name('customers.mail_models.edit');
        Route::post('/customers/mail-models/update', [MailerController::class, 'updateModel'])->name('customers.mail_models.update');
        Route::delete('/customers/mail-models/{id}', [MailerController::class, 'deleteModel'])->name('customers.mail_models.delete');

        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::post('/customers/profile-settings', [CustomerController::class, 'updateProfileSettings'])->name('customers.profile_settings');

        // Rotte setting

        Route::post('settings/numbers',  [SettingController::class, 'numbers'])->name('settings.numbers');
        Route::post('settings/advanced',  [SettingController::class, 'advanced'])->name('settings.advanced');
        Route::post('settings/updateAll',  [SettingController::class, 'updateAll'])->name('settings.updateAll');
        Route::post('settings/updateAree', [SettingController::class, 'updateAree'])->name('settings.updateAree');

        Route::post('categories/neworder', [CategoryController::class, 'neworder'])->name('categories.neworder');
        Route::post('categories/new_order_products', [CategoryController::class, 'new_order_products'])->name('categories.new_order_products');
        
        // Rotte product
        
        Route::get('products/archived', [ProductController::class, 'archived'])->name('products.archived');
        Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
        Route::get('products/{product}/quick-view', [ProductController::class, 'quickView'])->name('products.quick-view');
        Route::post('products/filter',  [ProductController::class, 'filter'])->name('products.filter');
        Route::post('products/status',  [ProductController::class, 'status'])->name('products.status');
        
        // Rotte order res
        Route::post('orders/changetime',       [OrderController::class, 'changetime'])->name('orders.changetime');
        Route::post('orders/status',       [OrderController::class, 'status'])->name('orders.status');
        Route::post('reservations/status', [ReservationController::class, 'status'])->name('reservations.status');
        
        // Rotte post
        
        Route::post('posts/neworder', [PostController::class, 'neworder'])->name('posts.neworder');
        Route::get('posts/archived',  [PostController::class, 'archived'])->name('posts.archived');
        Route::get('posts/search',    [PostController::class, 'search'])->name('posts.search');
        Route::get('posts/{post}/quick-view', [PostController::class, 'quickView'])->name('posts.quick-view');
        Route::post('posts/filter',   [PostController::class, 'filter'])->name('posts.filter');
        Route::post('posts/status',   [PostController::class, 'status'])->name('posts.status');

        // Rotte promotion marketing

        Route::post('promotions/{promotion}/publish', [PromotionController::class, 'publish'])->name('promotions.publish');
        Route::post('promotions/{promotion}/pause',   [PromotionController::class, 'pause'])->name('promotions.pause');
        Route::post('promotions/{promotion}/archive', [PromotionController::class, 'archive'])->name('promotions.archive');

        // Rotte campaign marketing

        Route::post('campaigns/{campaign}/activate', [CampaignController::class, 'activate'])->name('campaigns.activate');
        Route::post('campaigns/{campaign}/pause',    [CampaignController::class, 'pause'])->name('campaigns.pause');
        Route::post('campaigns/{campaign}/archive',  [CampaignController::class, 'archive'])->name('campaigns.archive');
        Route::post('campaigns/{campaign}/preview-audience', [CampaignController::class, 'previewAudience'])->name('campaigns.preview-audience');
        Route::post('campaigns/{campaign}/prepare-assignments', [CampaignController::class, 'prepareAssignments'])->name('campaigns.prepare-assignments');

        // Rotte automation marketing

        Route::post('automations/{automation}/activate', [AutomationController::class, 'activate'])->name('automations.activate');
        Route::post('automations/{automation}/pause',    [AutomationController::class, 'pause'])->name('automations.pause');
        Route::post('automations/{automation}/archive',  [AutomationController::class, 'archive'])->name('automations.archive');
        Route::post('automations/{automation}/preview-audience', [AutomationController::class, 'previewAudience'])->name('automations.preview-audience');
        Route::post('automations/{automation}/prepare-assignments', [AutomationController::class, 'prepareAssignments'])->name('automations.prepare-assignments');
        
        // Rotte Date 
        
        Route::post('dates/editDays',    [DateController::class, 'editDays'])->name('dates.editDays');
        Route::get('dates/showDay',    [DateController::class, 'showDay'])->name('dates.showDay');
        Route::post('dates/status',    [DateController::class, 'status'])->name('dates.status');
        Route::post('dates/blockTime', [DateController::class, 'blockTime'])->name('dates.blockTime');
        Route::post('/dates/generate', [DateController::class, 'generate'])->name('dates.generate');
        
        Route::post('orders/filter',       [OrderController::class, 'filter'])->name('orders.filter');
        Route::post('reservations/filter', [ReservationController::class, 'filter'])->name('reservations.filter');
        //resource
        Route::resource('allergens',     AllergenController::class);
        Route::resource('automations',   AutomationController::class)->except(['destroy']);
        Route::resource('menus',         MenuController::class);
        Route::resource('settings',      SettingController::class);
        Route::resource('campaigns',     CampaignController::class)->except(['destroy']);
        Route::resource('dates',         DateController::class);
        Route::resource('orders',        OrderController::class);
        Route::resource('products',      ProductController::class);
        Route::resource('promotions',    PromotionController::class)->except(['destroy']);
        Route::resource('posts',         PostController::class);
        Route::resource('reservations',  ReservationController::class);
        Route::resource('ingredients',   IngredientController::class);
        Route::resource('categories',    CategoryController::class);

        Route::get('/list', [AdminPageController::class, 'list'])->name('list');
        Route::get('/settings', [AdminPageController::class, 'settings'])->name('settings');
        Route::get('/menu', [AdminPageController::class, 'menu'])->name('menu');

        Route::post('settings/cancelDates',        [SettingController::class, 'cancelDates'])->name('settings.cancelDates');


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

//Route::get('/notifica',        [AdminPageController::class, 'sendNotification']);
