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

        Route::post('settings/numbers',       [SettingController::class, 'numbers'])->name('settings.numbers');
        Route::post('settings/advanced',      [SettingController::class, 'advanced'])->name('settings.advanced');
        Route::post('settings/updateAll',     [SettingController::class, 'updateAll'])->name('settings.updateAll');
        Route::post('settings/updateAree',    [SettingController::class, 'updateAree'])->name('settings.updateAree');
        Route::patch('settings/quick-update', [SettingController::class, 'quickUpdate'])->name('settings.quickUpdate');

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

        Route::get('promotions/archived', [PromotionController::class, 'archived'])->name('promotions.archived');
        Route::post('promotions/{promotion}/publish', [PromotionController::class, 'publish'])->name('promotions.publish');
        Route::post('promotions/{promotion}/pause',   [PromotionController::class, 'pause'])->name('promotions.pause');
        Route::post('promotions/{promotion}/archive', [PromotionController::class, 'archive'])->name('promotions.archive');
        Route::post('promotions/{promotion}/draft',   [PromotionController::class, 'draft'])->name('promotions.draft');
        Route::delete('promotions/{promotion}', [PromotionController::class, 'destroy'])->name('promotions.destroy');

        // Rotte campaign marketing

        Route::get('campaigns/audience-preview', [CampaignController::class, 'audiencePreview'])->name('campaigns.audience-preview');
        Route::get('campaigns/archived', [CampaignController::class, 'archived'])->name('campaigns.archived');
        Route::post('campaigns/{campaign}/activate', [CampaignController::class, 'activate'])->name('campaigns.activate');
        Route::post('campaigns/{campaign}/pause',    [CampaignController::class, 'pause'])->name('campaigns.pause');
        Route::post('campaigns/{campaign}/archive',  [CampaignController::class, 'archive'])->name('campaigns.archive');
        Route::post('campaigns/{campaign}/restore',  [CampaignController::class, 'restore'])->name('campaigns.restore');
        Route::post('campaigns/{campaign}/draft',    [CampaignController::class, 'draft'])->name('campaigns.draft');
        Route::post('campaigns/{campaign}/preview-audience', [CampaignController::class, 'previewAudience'])->name('campaigns.preview-audience');
        Route::post('campaigns/{campaign}/prepare-assignments', [CampaignController::class, 'prepareAssignments'])->name('campaigns.prepare-assignments');
        Route::delete('campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');

        // Rotte automation marketing

        Route::post('automations/{automation}/activate', [AutomationController::class, 'activate'])->name('automations.activate');
        Route::post('automations/{automation}/pause',    [AutomationController::class, 'pause'])->name('automations.pause');
        Route::post('automations/{automation}/archive',  [AutomationController::class, 'archive'])->name('automations.archive');
        Route::post('automations/{automation}/draft',    [AutomationController::class, 'draft'])->name('automations.draft');
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

// ============================================================
// ROTTE DI ANTEPRIMA EMAIL — solo ambienti non-produzione
// Parametri query:
//   ?status=0  → annullato  (default ordine: 2 = in attesa)
//   ?status=1  → confermato (default prenotazione: 1)
//   ?delivery=1 → mostra variante consegna a domicilio (solo ordini)
// URL:
//   /dev/mail-preview/order/admin
//   /dev/mail-preview/order/user
//   /dev/mail-preview/reservation/admin
//   /dev/mail-preview/reservation/user
// ============================================================
if (app()->environment('local', 'staging', 'development')) {

    Route::prefix('dev/mail-preview')->name('dev.mail.')->group(function () {

        // ---------- helper: costruisce mock stdClass ----------
        $mk = function (array $props): object {
            $obj = new \stdClass();
            foreach ($props as $k => $v) { $obj->$k = $v; }
            return $obj;
        };

        // ---------- mock carrello ordine ----------
        // Le immagini sono file reali presi da storage/app/public/uploads/
        $buildCart = function () use ($mk): array {
            $categoryPrimo  = $mk(['name' => 'Primi piatti']);
            $categorySecond = $mk(['name' => 'Secondi piatti']);
            $categoryDolci  = $mk(['name' => 'Dolci']);

            // Prodotto 1: pizza con foto, ingrediente rimosso e aggiunto
            $p1Pivot = $mk(['quantity' => 2, 'remove' => '["Origano"]', 'add' => '[]', 'option' => '[]']);
            $p1Add   = $mk(['name' => 'Doppia mozzarella', 'price' => 150]);
            $p1 = $mk([
                'name'     => 'Pizza Margherita',
                'price'    => 1200,
                'image'    => 'uploads/0j3wdjau1FKCkayonqd5JjkxaAKLT8LLQYO6gw8v.png',
                'pivot'    => $p1Pivot,
                'r_option' => collect([]),
                'r_add'    => collect([$p1Add]),
            ]);

            // Prodotto 2: carbonara con foto e opzione
            $p2Pivot  = $mk(['quantity' => 1, 'remove' => '[]', 'add' => '[]', 'option' => '[]']);
            $p2Option = $mk(['name' => 'Senza pepe', 'price' => 0]);
            $p2 = $mk([
                'name'     => 'Pasta alla Carbonara',
                'price'    => 1400,
                'image'    => 'uploads/1Ff9Ru1AAgGQi08MqJUUgfiNNdgzMuYgwoxrxcgJ.png',
                'pivot'    => $p2Pivot,
                'r_option' => collect([$p2Option]),
                'r_add'    => collect([]),
            ]);

            // Menu fisso con foto e scelte
            $menuSubP1 = $mk(['id' => 1, 'name' => 'Tagliolini al tartufo', 'category' => $categoryPrimo,  'pivot' => $mk(['label' => 'Primo',  'extra_price' => 200])]);
            $menuSubP2 = $mk(['id' => 2, 'name' => 'Filetto di manzo',      'category' => $categorySecond, 'pivot' => $mk(['label' => 'Secondo', 'extra_price' => 0])]);
            $menuSubP3 = $mk(['id' => 3, 'name' => 'Tiramisù artigianale',  'category' => $categoryDolci,  'pivot' => $mk(['label' => 'Dolce',  'extra_price' => 0])]);
            $menuPivot = $mk(['quantity' => 1, 'choices' => json_encode([1, 2, 3])]);
            $menu = $mk([
                'name'       => 'Menu Degustazione Chef',
                'price'      => 4500,
                'image'      => 'uploads/2VSuSNLEM3cmtbzoZQzOIMp2fz51TqzeYyl71GFN.png',
                'fixed_menu' => '2',
                'pivot'      => $menuPivot,
                'products'   => collect([$menuSubP1, $menuSubP2, $menuSubP3]),
            ]);

            return [
                'menus'    => collect([$menu]),
                'products' => collect([$p1, $p2]),
            ];
        };

        // ---------- ORDINE — ADMIN ----------
        Route::get('order/admin', function () use ($mk, $buildCart) {
            $status   = (int) request('status', 2);
            $delivery = (bool) request('delivery', 0);

            $content_mail = [
                'type'     => 'or',
                'to'       => 'admin',
                'status'   => $status,
                'order_id' => 42,

                'title'    => 'Mario Rossi ha appena fatto un ordine ' . ($delivery ? 'a domicilio' : 'd\'asporto'),
                'subtitle' => '',

                'name'       => 'Mario',
                'surname'    => 'Rossi',
                'email'      => 'mario.rossi@example.com',
                'phone'      => '3391234567',
                'admin_phone'=> '0612345678',
                'date_slot'  => '20/07/2025 20:30',
                'message'    => 'Per favore, pizza ben cotta e tagliata in 8 spicchi.',

                'cart'        => $buildCart(),
                'total_price' => 8300,

                'comune'       => $delivery ? 'Roma'      : null,
                'address'      => $delivery ? 'Via Veneto' : null,
                'address_n'    => $delivery ? '123'        : null,
                'delivery_cost'=> $delivery ? 250          : 0,

                'whatsapp_message_id' => null,

                'promotions' => [[
                    'promotion_name' => 'Sconto Benvenuto',
                    'type_label'     => 'Sconto percentuale – 10%',
                    'type_discount'  => 'percentage',
                    'discount_amount'=> 830,
                    'affected_items' => [],
                ]],

                'property_adv' => ['dt' => false, 'sala_1' => 'Sala Nord', 'sala_2' => 'Sala Sud'],
            ];

            return view('emails.confermaOrderAdmin', compact('content_mail'));
        })->name('order.admin');

        // ---------- ORDINE — UTENTE ----------
        Route::get('order/user', function () use ($mk, $buildCart) {
            $status   = (int) request('status', 1);
            $delivery = (bool) request('delivery', 0);

            $content_mail = [
                'type'     => 'or',
                'to'       => 'user',
                'status'   => $status,
                'order_id' => 42,

                'title'    => $status == 1
                    ? 'Ciao Mario, il tuo ordine è stato confermato!'
                    : ($status == 0
                        ? 'Ci dispiace, il tuo ordine è stato annullato'
                        : 'Ciao Mario, grazie per aver ordinato tramite il nostro sito'),
                'subtitle' => $status == 6
                    ? 'Il tuo rimborso verrà elaborato in 5-10 giorni lavorativi'
                    : ($status == 2 ? 'Il tuo ordine è nella nostra coda, a breve riceverai l\'esito' : ''),

                'name'       => 'Mario',
                'surname'    => 'Rossi',
                'email'      => 'mario.rossi@example.com',
                'phone'      => '3391234567',
                'admin_phone'=> '0612345678',
                'date_slot'  => '20/07/2025 20:30',
                'message'    => null,

                'cart'        => $buildCart(),
                'total_price' => 8300,

                'comune'       => $delivery ? 'Roma'      : null,
                'address'      => $delivery ? 'Via Veneto' : null,
                'address_n'    => $delivery ? '123'        : null,
                'delivery_cost'=> $delivery ? 250          : 0,

                'whatsapp_message_id' => 'wamid.test123',

                'promotions' => [[
                    'promotion_name' => 'Sconto Benvenuto',
                    'type_label'     => 'Sconto percentuale – 10%',
                    'type_discount'  => 'percentage',
                    'discount_amount'=> 830,
                    'affected_items' => [],
                ]],

                'property_adv' => ['dt' => false, 'sala_1' => 'Sala Nord', 'sala_2' => 'Sala Sud'],
            ];

            // Simula la subscription per mostrare il pulsante annulla
            config(['configurazione.subscription' => 3]);

            return view('emails.confermaOrderAdmin', compact('content_mail'));
        })->name('order.user');

        // ---------- PRENOTAZIONE — ADMIN ----------
        Route::get('reservation/admin', function () use ($mk) {
            $status = (int) request('status', 2);

            $content_mail = [
                'type'     => 'res',
                'to'       => 'admin',
                'status'   => $status,
                'res_id'   => 17,

                'title'    => 'Nuova prenotazione da Giulia Bianchi',
                'subtitle' => '',

                'name'       => 'Giulia',
                'surname'    => 'Bianchi',
                'email'      => 'giulia.bianchi@example.com',
                'phone'      => '3476543210',
                'admin_phone'=> '0612345678',
                'date_slot'  => '25/07/2025 13:00',
                'message'    => 'Siamo celiaci, confermate che avete opzioni senza glutine?',

                'sala'     => 1,
                'n_person' => json_encode(['adult' => 3, 'child' => 1]),

                'whatsapp_message_id' => null,

                'promotions' => [[
                    'promotion_name' => 'Pranzo di Famiglia',
                    'type_label'     => 'Omaggio',
                    'type_discount'  => 'gift',
                    'discount_amount'=> 0,
                    'affected_items' => [['name' => 'Dessert per bambini']],
                ]],

                'property_adv' => [
                    'dt'     => true,
                    'sala_1' => 'Sala Principale',
                    'sala_2' => 'Terrazza Esterna',
                ],
            ];

            return view('emails.confermaOrderAdmin', compact('content_mail'));
        })->name('reservation.admin');

        // ---------- PRENOTAZIONE — UTENTE ----------
        Route::get('reservation/user', function () use ($mk) {
            $status = (int) request('status', 1);

            $content_mail = [
                'type'     => 'res',
                'to'       => 'user',
                'status'   => $status,
                'res_id'   => 17,

                'title'    => $status == 1
                    ? 'Giulia, la tua prenotazione è confermata!'
                    : 'Ci dispiace, la tua prenotazione è stata annullata',
                'subtitle' => $status == 1 ? 'Ti aspettiamo!' : '',

                'name'       => 'Giulia',
                'surname'    => 'Bianchi',
                'email'      => 'giulia.bianchi@example.com',
                'phone'      => '3476543210',
                'admin_phone'=> '0612345678',
                'date_slot'  => '25/07/2025 13:00',
                'message'    => 'Siamo celiaci, confermate che avete opzioni senza glutine?',

                'sala'     => 1,
                'n_person' => json_encode(['adult' => 3, 'child' => 1]),

                'whatsapp_message_id' => 'wamid.test456',

                'promotions' => [[
                    'promotion_name' => 'Pranzo di Famiglia',
                    'type_label'     => 'Omaggio',
                    'type_discount'  => 'gift',
                    'discount_amount'=> 0,
                    'affected_items' => [],
                ]],

                'property_adv' => [
                    'dt'     => true,
                    'sala_1' => 'Sala Principale',
                    'sala_2' => 'Terrazza Esterna',
                ],
            ];

            // Simula subscription per mostrare pulsante annulla
            config(['configurazione.subscription' => 3]);

            return view('emails.confermaOrderAdmin', compact('content_mail'));
        })->name('reservation.user');

    });
}
