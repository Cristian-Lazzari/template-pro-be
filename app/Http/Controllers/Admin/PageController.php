<?php



namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Date;
use App\Models\Menu;
use App\Models\Post;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    
    public function statistics()
    {
        // Grafico a torta: Prodotti più ordinati
        $topProducts = DB::table('order_product')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(10) // Puoi variare questo valore per vedere più prodotti
            ->get()
            ->mapWithKeys(function ($item) {
                $product = Product::find($item->product_id);
                return [$product->name => $item->total_quantity];
            });

        // Grafico a colonne: Ordinazioni nel tempo
        $ordersOverTime = DB::table('order_product')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->join('products', 'order_product.product_id', '=', 'products.id')
            ->select(
                DB::raw("DATE_FORMAT(STR_TO_DATE(orders.date_slot, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as day"), 
                'products.name as product', 
                DB::raw('SUM(order_product.quantity) as quantity')
            )
            ->groupBy('day', 'products.name')
            ->orderBy('day')
            ->get();

        // Ristruttura i dati per il grafico
        $chartData = [];
        foreach ($ordersOverTime as $order) {
            $chartData[$order->day][$order->product] = $order->quantity;
        }

        // Riordina in formato leggibile
        $labels = array_keys($chartData); // Le date
        $datasets = [];

        // Ottieni i prodotti unici
        $allProducts = DB::table('products')->pluck('name')->toArray();

        // Creazione dei dataset per ogni prodotto
        foreach ($allProducts as $product) {
            $dataset = [
                'label' => $product,
                'data' => [],
            ];
            foreach ($labels as $label) {
                $dataset['data'][] = $chartData[$label][$product] ?? 0; // Aggiungi 0 se non ci sono dati
            }
            $datasets[] = $dataset;
        }


        // Grafico a linee: Ricavi nel tempo

        // Estrarre i dati dalla tabella Orders
        $orders = Order::selectRaw("DATE_FORMAT(STR_TO_DATE(`date_slot`, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date, status, SUM(tot_price) as total_price")
            ->whereIn('status', [0, 1, 5, 6]) // Considera solo i status rilevanti
           // ->where('status', '!=', 4) // Escludi status = 4
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        // Formattare i dati per il frontend
        $revenueOverTime = [
            'paid' => [],
            'cod' => [],
            'canceled' => [],
            'tot' => [],
        ];
        $xdate = 0;

        foreach ($orders as $order) {
            $point = [
                'x' => $order->date,
                'y' => $order->total_price / 100,
            ];
            switch ($order->status) {
                case 5:
                    $revenueOverTime['paid'][] = $point;
                    break;
                case 1:
                    $revenueOverTime['cod'][] = $point;
                    break;
                case 6:
                    $revenueOverTime['canceled'][] = $point;
                    break;
                case 0:
                    $revenueOverTime['canceled'][] = $point;
                    break;
                }
            
            if($xdate !== $point['x']){
                $revenueOverTime['tot'][] = $point;
            }else{
                $revenueOverTime['tot'][count($revenueOverTime['tot']) - 1]['y'] += $point['y'];
            }


            $xdate = $point['x'];
        }
       // dd('fine');

        $reservations = DB::table('reservations')
        ->selectRaw("DATE_FORMAT(STR_TO_DATE(`date_slot`, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date, SUM(JSON_EXTRACT(n_person, '$.adult')) as adults, SUM(JSON_EXTRACT(n_person, '$.child')) as children")
        ->groupBy('date')
        ->orderBy('date', 'ASC')
        ->get();

        $order_count = Order::where('status', '!=', 4)->count();
        $res_count = Reservation::where('status', '!=', 4)->count();

        return view('admin.statistics', [
            'topProducts' => $topProducts,
            'labels' => $labels,
            'datasets' => $datasets,
            'revenueOverTime' => $revenueOverTime,
            'reservations' => $reservations,
            'order_count' => $order_count,
            'res_count' => $res_count,
        ]);
    }

    public function dashboard() {   
        $property_adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        if(config('configurazione.subscription') == 1){
            $menus = Menu::where('promo', 1)->get();
            $products = Product::where('promotion', 1)->get();
            return view('admin.menu', compact('menus', 'products'));
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
            if(array_key_exists($r->day, $reserved)){
                $reserved[$r->day]['or'][] = $day;
            }else{
                $reserved[$r->day]['or'][] = $day;
            }
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
                ];
                
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
            $monthNumber = $day['month'];
            $year = $day['year'];

            // se il mese non esiste ancora, inizializzalo
            if (!isset($result[$monthNumber])) {
                $result[$monthNumber] = [
                    'year' => $year,
                    'month' => $monthNumber,
                    'days' => [],
                    'n_order' => 0,
                    'n_res' => 0,
                    'guests' => 0,
                    'cash' => 0,
                ];
            }

            // aggiungi il giorno dentro il mese corrispondente
            $result[$monthNumber]['days'][] = $day;
            $result[$monthNumber]['n_order'] += $day['n_order'];
            $result[$monthNumber]['n_res'] += $day['n_res'];
            $result[$monthNumber]['cash'] += $day['cash'];
            $result[$monthNumber]['guests'] += $day['guests'];
        }
       // dd($result);


        return $result;
    }
    public function settings(){
        $setting = Setting::all()->keyBy('name');
        return view('admin.settings', compact('setting'));
    }
    public function menu(){

        $menus = Menu::where('fixed_menu', '!=', '0')->where('promo', 1)->with('products', 'category')->orderBy('updated_at', 'desc')->get();
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

        $products = Product::where('promotion', 1)->get();

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
        ];
        
            

        return view('admin.menu', compact('menus', 'products', 'stat'));
    }
}
