<?php



namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allergen;
use App\Models\Automation;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Date;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Model as MailModel;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Reservation;
use App\Models\Setting;
use App\Support\Currency;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PageController extends Controller
{
    
    public function statistics()
    {
        $locale = app()->getLocale();
        $activeStatuses = [1, 2, 3, 5];
        $cancelledStatuses = [0, 6];
        $adultCountSql = 'CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(n_person, \'$.adult\')), 0) AS UNSIGNED)';
        $childCountSql = 'CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(n_person, \'$.child\')), 0) AS UNSIGNED)';

        $productSeries = DB::table('order_product')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->join('products', 'order_product.product_id', '=', 'products.id')
            ->leftJoin('product_translations', function ($join) use ($locale) {
                $join->on('products.id', '=', 'product_translations.product_id')
                    ->where('product_translations.lang', $locale);
            })
            ->where('orders.status', '!=', 4)
            ->selectRaw("DATE_FORMAT(STR_TO_DATE(orders.date_slot, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date")
            ->selectRaw('products.id as product_id')
            ->selectRaw("COALESCE(product_translations.name, CONCAT('Prodotto #', products.id)) as product")
            ->selectRaw('SUM(order_product.quantity) as quantity')
            ->groupBy('date', 'products.id', 'product_translations.name')
            ->orderBy('date')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->date,
                    'product_id' => (int) $row->product_id,
                    'product' => $row->product,
                    'quantity' => (int) $row->quantity,
                ];
            })
            ->values();

        $ordersDaily = DB::table('orders')
            ->where('status', '!=', 4)
            ->selectRaw("DATE_FORMAT(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date")
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(CASE WHEN status IN (1, 2, 3, 5) THEN 1 ELSE 0 END) as confirmed_orders')
            ->selectRaw('SUM(CASE WHEN status IN (0, 6) THEN 1 ELSE 0 END) as cancelled_orders')
            ->selectRaw('SUM(tot_price) as total_revenue_cents')
            ->selectRaw('SUM(CASE WHEN status IN (1, 2, 3, 5) THEN tot_price ELSE 0 END) as confirmed_revenue_cents')
            ->selectRaw('SUM(CASE WHEN status = 5 THEN tot_price ELSE 0 END) as paid_revenue_cents')
            ->selectRaw('SUM(CASE WHEN status = 1 THEN tot_price ELSE 0 END) as cod_revenue_cents')
            ->selectRaw('SUM(CASE WHEN status IN (0, 6) THEN tot_price ELSE 0 END) as cancelled_revenue_cents')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->date,
                    'total_orders' => (int) $row->total_orders,
                    'confirmed_orders' => (int) $row->confirmed_orders,
                    'cancelled_orders' => (int) $row->cancelled_orders,
                    'total_revenue_cents' => (float) $row->total_revenue_cents,
                    'confirmed_revenue_cents' => (float) $row->confirmed_revenue_cents,
                    'paid_revenue_cents' => (float) $row->paid_revenue_cents,
                    'cod_revenue_cents' => (float) $row->cod_revenue_cents,
                    'cancelled_revenue_cents' => (float) $row->cancelled_revenue_cents,
                ];
            })
            ->values();

        $reservationsDaily = DB::table('reservations')
            ->where('status', '!=', 4)
            ->selectRaw("DATE_FORMAT(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date")
            ->selectRaw('COUNT(*) as total_reservations')
            ->selectRaw('SUM(CASE WHEN status IN (1, 2, 3, 5) THEN 1 ELSE 0 END) as confirmed_reservations')
            ->selectRaw('SUM(CASE WHEN status IN (0, 6) THEN 1 ELSE 0 END) as cancelled_reservations')
            ->selectRaw("SUM({$adultCountSql}) as adults_total")
            ->selectRaw("SUM({$childCountSql}) as children_total")
            ->selectRaw("SUM(CASE WHEN status IN (1, 2, 3, 5) THEN {$adultCountSql} ELSE 0 END) as adults_confirmed")
            ->selectRaw("SUM(CASE WHEN status IN (1, 2, 3, 5) THEN {$childCountSql} ELSE 0 END) as children_confirmed")
            ->selectRaw("SUM(CASE WHEN status IN (0, 6) THEN {$adultCountSql} ELSE 0 END) as adults_cancelled")
            ->selectRaw("SUM(CASE WHEN status IN (0, 6) THEN {$childCountSql} ELSE 0 END) as children_cancelled")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => $row->date,
                    'total_reservations' => (int) $row->total_reservations,
                    'confirmed_reservations' => (int) $row->confirmed_reservations,
                    'cancelled_reservations' => (int) $row->cancelled_reservations,
                    'adults_total' => (int) $row->adults_total,
                    'children_total' => (int) $row->children_total,
                    'adults_confirmed' => (int) $row->adults_confirmed,
                    'children_confirmed' => (int) $row->children_confirmed,
                    'adults_cancelled' => (int) $row->adults_cancelled,
                    'children_cancelled' => (int) $row->children_cancelled,
                ];
            })
            ->values();

        $orderCount = (int) $ordersDaily->sum('total_orders');
        $reservationCount = (int) $reservationsDaily->sum('total_reservations');
        $guestsCount = (int) $reservationsDaily->sum(function ($row) {
            return $row['adults_total'] + $row['children_total'];
        });

        $topProduct = $productSeries
            ->groupBy('product')
            ->map(function ($rows) {
                return $rows->sum('quantity');
            })
            ->sortDesc()
            ->keys()
            ->first();

        $bestRevenueDay = $ordersDaily->sortByDesc('confirmed_revenue_cents')->first();
        $bestReservationDay = $reservationsDaily->sortByDesc('total_reservations')->first();

        $activityDates = $ordersDaily->pluck('date')
            ->merge($reservationsDaily->pluck('date'))
            ->merge($productSeries->pluck('date'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $activityRange = [
            'start' => $activityDates->first(),
            'end' => $activityDates->last(),
            'days' => $activityDates->isNotEmpty()
                ? Carbon::parse($activityDates->first())->diffInDays(Carbon::parse($activityDates->last())) + 1
                : 0,
        ];

        $summary = [
            'order_count' => $orderCount,
            'confirmed_orders' => (int) $ordersDaily->sum('confirmed_orders'),
            'cancelled_orders' => (int) $ordersDaily->sum('cancelled_orders'),
            'reservation_count' => $reservationCount,
            'confirmed_reservations' => (int) $reservationsDaily->sum('confirmed_reservations'),
            'cancelled_reservations' => (int) $reservationsDaily->sum('cancelled_reservations'),
            'guests' => $guestsCount,
            'confirmed_revenue' => Currency::roundAmount($ordersDaily->sum('confirmed_revenue_cents')),
            'average_ticket' => $orderCount > 0
                ? Currency::roundAmount($ordersDaily->sum('confirmed_revenue_cents') / $orderCount)
                : 0,
            'average_guests' => $reservationCount > 0 ? round($guestsCount / $reservationCount, 1) : 0,
            'top_product' => $topProduct,
            'best_revenue_day' => $bestRevenueDay,
            'best_reservation_day' => $bestReservationDay,
        ];

        $statisticsPayload = [
            'today' => now()->toDateString(),
            'activity' => $activityRange,
            'ordersDaily' => $ordersDaily->all(),
            'reservationsDaily' => $reservationsDaily->all(),
            'productSeries' => $productSeries->all(),
        ];

        return view('admin.statistics', [
            'summary' => $summary,
            'activityRange' => $activityRange,
            'statisticsPayload' => $statisticsPayload,
            'hasStatistics' => $orderCount > 0 || $reservationCount > 0,
        ]);
    }

    public function dashboard() {   
        $property_adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        if(config('configurazione.subscription') == 1){
            $menuInt = $this->menu_int();
            $products = $menuInt['products'];
            $menus = $menuInt['menus'];
            $stat = $menuInt['stat'];
            return view('admin.menu', compact('menus', 'products', 'stat'));
        }
        $calendar = $this->get_date();
        $notify = [];
        return view('admin.dashboard', compact('calendar', 'notify','property_adv'));
    }
    private function get_res(){

        $reservations = DB::table('reservations')
            ->select(
                'name',
                'surname',
                'status',
                'n_person',
                'id',
                'status',
                DB::raw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'))  AS day"),
                DB::raw("DATE_FORMAT(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), '%H:%i') AS time")
            )
            ->where('status', '!=', 4) // 👈 controllo aggiunto
            ->orderByRaw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) ASC")
            ->orderByRaw("TIME(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) ASC")
        ->get();
        $orders = Order::select(
                'name',
                'surname',
                'status',
                'tot_price',
                'id',
                'status',
                DB::raw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'))  AS day"),
                DB::raw("DATE_FORMAT(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), '%H:%i') AS time")
            )
            ->where('status', '!=', 4) // 👈 controllo aggiunto
            ->orderByRaw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) ASC")
            ->orderByRaw("TIME(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) ASC")
            ->with(['products', 'menus']) // 👈 carico anche i prodotti e i menu
        ->get();

        
        $reserved = [];
        foreach ($reservations as $r) {
            $day = $r;
            $reserved[$r->day]['res'][] = $day;
        }
        foreach ($orders as $r) {
            $day = $r;
            $reserved[$r->day]['or'][] = $day;
        }
        return $reserved;
    }
    private function get_date(){
        $reserved = $this->get_res();
        
        $firstKey = count($reserved) ? array_key_first($reserved) : '';
        $first_day = $firstKey !== '' ? Carbon::createFromFormat('Y-m-d', $firstKey) : Carbon::now();

        $adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        $week = $adv['week_set'];

        $now = Carbon::now(); 
        $day_in_calendar = $first_day->diffInDays($now) + 60; // giorni da mostrare
        $days = [];
        for ($i = 0 ; $i < $day_in_calendar; $i++) { 
            $day = [
                'year' => $first_day->year,
                'month' => $first_day->month, // 1 - 12
                'date' => $first_day->copy()->format('Y-m-d'),
                'day' => $first_day->copy()->format('j'), // 1 - 31
                'day_w' => $first_day->copy()->format('N'), // 1 = lunedì, 7 = domenica
                'times' => [],
                'status' => 1, // 0 non disponibile,1 disponobile,2 oggi,  3 bloccato
                'guests' => 0,
                'n_order' => 0,
                'n_res' => 0,
                'cash' => 0,
            ];


            if (count($week[$first_day->format('N')]) == 0) {
                $day['status'] = 0;
            }

            if(isset($adv['day_off']) && in_array($first_day->copy()->format('Y-m-d'), $adv['day_off'])) {
                $day['status'] = 3;
            }
            if($day['day'] == $now->copy()->format('Y-m-d')){
                $day['status'] = 2;
            }

            foreach ($week[$first_day->format('N')] as $time => $property) {
                $day['times'][$time] = [
                    'res' => [],
                    'or' => [],
                    'property' => $property,
                    'blocked' => false,
                ];
            }

            $blockedTimes = $adv['time_blocked'] ?? [];
            if (isset($blockedTimes[$day['date']]) && is_array($blockedTimes[$day['date']])) {
                foreach ($blockedTimes[$day['date']] as $blockedTime) {
                    if (isset($day['times'][$blockedTime])) {
                        $day['times'][$blockedTime]['blocked'] = true;
                    } else {
                        // in caso sia un orario bloccato non nella configurazione standard, aggiungilo ugualmente.
                        $day['times'][$blockedTime] = [
                            'res' => [],
                            'or' => [],
                            'property' => [],
                            'blocked' => true,
                        ];
                    }
                }
            }

            if(isset($reserved[$day['date']])){
                foreach ($reserved[$day['date']]['res'] ?? [] as $r) {

                    if(isset($day['times'][$r->time])){
                        $day['times'][$r->time]['res'][] = $r;
                    }else{
                        $day['times'][$r->time] = [
                            'res' => [$r],
                            'or' => [],
                            'property' => [],
                        ];
                    }
                    $_p = json_decode($r->n_person);
                    $day['guests'] += ($_p->child + $_p->adult);
                    $day['n_res'] ++ ;
                }
                foreach ($reserved[$day['date']]['or'] ?? [] as $r) {
                    if(isset($day['times'][$r->time])){
                        $day['times'][$r->time]['or'][] = $r;
                    }else{
                        $day['times'][$r->time] = [
                            'res' => [],
                            'or' => [$r],
                            'property' => [],
                        ];
                    }
                    $day['n_order'] ++ ;
                    $day['cash'] += $r->tot_price;
                }
            }
            uksort($day['times'], function($a, $b) {
                // confronto come orari
                return strtotime($a) <=> strtotime($b);
            });
 
            
            
            $days[] = $day;
            
            $first_day->addDay();
        }
        $result = [];

        foreach ($days as $day) {
            $month = (int) $day['month'];
            $year  = (int) $day['year'];

            // chiave tecnica unica (non cambia la struttura finale)
            $key = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

            // se il mese non esiste ancora, inizializzalo
            if (!isset($result[$key])) {
                $result[$key] = [
                    'year' => $year,
                    'month' => $month,
                    'days' => [],
                    'n_order' => 0,
                    'n_res' => 0,
                    'guests' => 0,
                    'cash' => 0,
                ];
            }

            // aggiungi il giorno dentro il mese corrispondente
            $result[$key]['days'][] = $day;
            $result[$key]['n_order'] += $day['n_order'];
            $result[$key]['n_res'] += $day['n_res'];
            $result[$key]['cash'] += $day['cash'];
            $result[$key]['guests'] += $day['guests'];
        }
       // dd($result);


        return $result;
    }
    public function settings(){
        $setting = Setting::all()->keyBy('name');
        $supportedCurrencies = Currency::supported();
        $activeCurrency = Currency::definition();

        return view('admin.settings', compact('setting', 'supportedCurrencies', 'activeCurrency'));
    }
    public function menu(){
        
        $menuInt = $this->menu_int();

        $products = $menuInt['products'];
        $menus = $menuInt['menus'];
        $stat = $menuInt['stat'];

        return view('admin.menu', compact('menus', 'products', 'stat'));
    }

    public function marketing()
    {
        $stat = [
            'promotions' => [
                'tot' => Schema::hasTable('promotions') ? Promotion::count() : 0,
                'active' => Schema::hasTable('promotions') ? Promotion::where('status', 'active')->count() : 0,
                'draft' => Schema::hasTable('promotions') ? Promotion::where('status', 'draft')->count() : 0,
                'archived' => Schema::hasTable('promotions') ? Promotion::where('status', 'archived')->count() : 0,
            ],
            'campaigns' => [
                'tot' => Schema::hasTable('campaigns') ? Campaign::count() : 0,
                'active' => Schema::hasTable('campaigns') ? Campaign::where('status', 'active')->count() : 0,
                'draft' => Schema::hasTable('campaigns') ? Campaign::where('status', 'draft')->count() : 0,
                'sent' => Schema::hasTable('campaigns') ? Campaign::where('status', 'sent')->count() : 0,
            ],
            'automations' => [
                'tot' => Schema::hasTable('automations') ? Automation::count() : 0,
                'active' => Schema::hasTable('automations') ? Automation::where('status', 'active')->count() : 0,
                'draft' => Schema::hasTable('automations') ? Automation::where('status', 'draft')->count() : 0,
                'paused' => Schema::hasTable('automations') ? Automation::where('status', 'paused')->count() : 0,
            ],
            'models' => [
                'tot' => $this->mailModelCount(),
            ],
        ];

        $latestPromotions = Schema::hasTable('promotions')
            ? Promotion::query()->latest('updated_at')->limit(5)->get()
            : collect();

        $latestCampaigns = Schema::hasTable('campaigns')
            ? Campaign::query()->with('promotions')->latest('updated_at')->limit(5)->get()
            : collect();

        $latestAutomations = Schema::hasTable('automations')
            ? Automation::query()->with('promotions')->latest('updated_at')->limit(5)->get()
            : collect();

        $latestMailModels = Schema::hasTable('models')
            ? $this->mailModelQuery()->latest('updated_at')->limit(5)->get()
            : collect();

        return view('admin.marketing', compact(
            'stat',
            'latestPromotions',
            'latestCampaigns',
            'latestAutomations',
            'latestMailModels'
        ));
    }

    private function mailModelCount(): int
    {
        if (! Schema::hasTable('models')) {
            return 0;
        }

        return $this->mailModelQuery()->count();
    }

    private function mailModelQuery()
    {
        $hasType = Schema::hasColumn('models', 'type');
        $hasChannel = Schema::hasColumn('models', 'channel');

        return MailModel::query()
            ->when($hasType || $hasChannel, function ($query) use ($hasType, $hasChannel) {
                $query->where(function ($nested) use ($hasType, $hasChannel) {
                    if ($hasType) {
                        $nested->orWhere('type', 'marketing');
                    }

                    if ($hasChannel) {
                        $nested->orWhere('channel', 'email');
                    }
                });
            });
    }

    protected function menu_int(){
        $menus = Menu::where('fixed_menu', '!=', '0')
            ->where('promo', 1)
            ->with(['products.category', 'category'])
            ->orderBy('updated_at', 'desc')
            ->get();
        foreach ($menus as $c) {
            if($c->fixed_menu == '2'){
                $choices = [];
                foreach ($c->products as $item) {
                    $label = $item->pivot->label;
                    if (!isset($choices[$label])) {
                        $choices[$label] = [];
                    }
                    $choices[$label][] = $item;
                }
                $c->fixed_menu = $choices;
            }
        }
        $products = Product::where('promotion', 1)
            ->with(['category', 'ingredients'])
            ->orderBy('updated_at', 'desc')
            ->get();
        $totalProducts = Product::count();
        $nonArchivedProducts = Product::where('archived', false)->count();
        $archivedProducts = Product::where('archived', true)->count();
        $nonArchivedVisibleProducts = Product::where('archived', false)
        ->where('visible', true)
            ->count();

        $stat = [
            'products' => [
                'tot' => $totalProducts,
                'not_archived' => $nonArchivedProducts,
                'archived' => $archivedProducts,
                'not_archived_visible' => $nonArchivedVisibleProducts,
            ],
            'categories' => [
                'tot' => Category::count(),
            ],
            'ingredients' => [
                'tot' => Ingredient::count(),
            ],
            'menus' => [
                'tot' => Menu::count(),
            ], 
            'allergens' => [
                'tot' => Allergen::count(),
            ], 
        ];
        $menu_int = [
            'stat' => $stat,
            'products' => $products,
            'menus' => $menus,
        ];
        return $menu_int;
    }
}
