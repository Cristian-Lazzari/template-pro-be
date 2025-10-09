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

        return view('admin.statistics', [
            'topProducts' => $topProducts,
            'labels' => $labels,
            'datasets' => $datasets,
            'revenueOverTime' => $revenueOverTime,
            'reservations' => $reservations,
        ]);
    }

    public function dashboard() {
        $calendar = $this->get_date();

        $property_adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);

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

        // dump($orders);
        // dump($reservations);
        // dd($reserved);
        


        return $reserved;
    }
    private function get_date(){
   
        $oldestDate_r = Reservation::orderBy('date_slot', 'asc')->value('date_slot');

        $oldestDate_o = Order::orderBy('date_slot', 'asc')->value('date_slot');

        $oldestCarbon = '';
        if($oldestDate_o){
            $oldestCarbon_o = Carbon::createFromFormat('d/m/Y H:i', $oldestDate_o);
            $oldestCarbon =  $oldestCarbon_o;
        }
        if($oldestDate_r){
            $oldestCarbon_r = Carbon::createFromFormat('d/m/Y H:i', $oldestDate_r);
            $oldestCarbon =  $oldestCarbon_r;
        }
        if($oldestDate_o && $oldestDate_r){
            $oldestCarbon =  $oldestCarbon_o->min($oldestCarbon_r);
        }
        if(!$oldestDate_o && !$oldestDate_r){
            $oldestCarbon =  Carbon::now();
        }

       
        
        $reserved = $this->get_res();

        $firstKey = array_key_first($reserved);
        $first_day = Carbon::createFromFormat('Y-m-d', $firstKey);

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
        $menus = Menu::where('promo', 1)->get();
        $products = Product::where('promotion', 1)->get();
        return view('admin.menu', compact('menus', 'products'));
    }



}

// public function dashboard() {

//         $setting = Setting::all()->keyBy('name');
//         $product_ = [
//             1 => Product::where('visible', 1)->where('archived', 0)->count(),
//             2 => Product::where('archived', 1)->count(),
//         ];
//         $stat = [
//             1 => Category::count(),
//             2 => Ingredient::count(),
//         ];
//         $meseCorrenteInizio = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
//         $meseCorrenteFine = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
//         $traguard = [
//             1 =>  Order::whereBetween(DB::raw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')"), [$meseCorrenteInizio, $meseCorrenteFine])->sum('tot_price'),
//             2 =>  Order::sum('tot_price'),
//             3 =>  Reservation::selectRaw('SUM(JSON_EXTRACT(n_person, "$.adult") + JSON_EXTRACT(n_person, "$.child")) AS total_persons')
//                 ->whereBetween(DB::raw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')"), [
//                     Carbon::now()->startOfMonth()->format('Y-m-d H:i:s'),
//                     Carbon::now()->endOfMonth()->format('Y-m-d H:i:s')
//                 ])
//                 ->value('total_persons'),
//             4 =>  Reservation::selectRaw('SUM(JSON_EXTRACT(n_person, "$.adult") + JSON_EXTRACT(n_person, "$.child")) AS total_persons')
//                 ->whereBetween(DB::raw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')"), [
//                     Carbon::now()->startOfYear()->format('Y-m-d H:i:s'),
//                     Carbon::now()->endOfYear()->format('Y-m-d H:i:s')
//                 ])
//                 ->value('total_persons')
//         ];
//         $post = [ 
//             1 => Post::count(),
//             2 => Post::where('visible', 0)->count(),
//             3 => Post::where('visible', 1)->where('archived', 0)->count(),
//             4 => Post::where('archived', 1)->count(),
//         ];
//         $order = [ 
//             1 => Order::where('status', 1)->count() + Order::where('status', 5)->count(),
//             2 => Order::where('status', 2)->count(),
//             3 => Order::where('status', 0)->count() + Order::where('status', 6)->count(),
//             4 => Order::where('status', 3)->count(),
//         ];
//         $reservation = [
//             1 => Reservation::where('status', 1)->count() + Reservation::where('status', 5)->count(),
//             2 => Reservation::where('status', 2)->count(),
//             3 => Reservation::where('status', 0)->count(),
//             4 => Reservation::where('status', 3)->count(),
//         ];
//         // Fetch and group Reservations by date
//         $reservations = Reservation::selectRaw("DATE_FORMAT(STR_TO_DATE(`date_slot`, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date, COUNT(*) as count")
//             ->groupBy('date')
//             ->orderByRaw("STR_TO_DATE(date, '%Y-%m-%d')")
//             ->get()
//             ->keyBy('date');

//         // Fetch and group Orders by date for delivery and asporto
//         $ordersDelivery = Order::whereNotNull('comune')
//             ->selectRaw("DATE_FORMAT(STR_TO_DATE(`date_slot`, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date, COUNT(*) as count")
//             ->groupBy('date')
//             ->orderByRaw("STR_TO_DATE(date, '%Y-%m-%d')")
//             ->get()
//             ->keyBy('date');

//         $ordersAsporto = Order::whereNull('comune')
//             ->selectRaw("DATE_FORMAT(STR_TO_DATE(`date_slot`, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date, COUNT(*) as count")
//             ->groupBy('date')
//             ->orderByRaw("STR_TO_DATE(date, '%Y-%m-%d')")
//             ->get()
//             ->keyBy('date');

//         // Combine all dates for consistency in the chart
//         $allDates = collect(array_merge(
//             $reservations->keys()->toArray(),
//             $ordersDelivery->keys()->toArray(),
//             $ordersAsporto->keys()->toArray()
//         ))->unique()->sort();

//         // Prepare the data for the chart
//        // Prepare the data for the chart

//         $chartData = [
//             'labels' => $allDates->map(fn($date) => Carbon::parse($date))->values()->toArray(),
//             'datasets' => [
//                 [
//                     'label' => 'Prenotazioni',
//                     'data' => $allDates->map(fn($date) => isset($reservations[$date]) ? (int)$reservations[$date]['count'] : 0)->values()->toArray(),
                    
//                     'borderColor' => '#090333',
//                     'backgroundColor' => '#090333',
//                 ],
//                 [
//                     'label' => ' Delivery',
//                     'data' => $allDates->map(fn($date) => isset($ordersDelivery[$date]) ? (int)$ordersDelivery[$date]['count'] : 0)->values()->toArray(),
//                     'borderColor' => '#10b793',
//                     'backgroundColor' => '#10b793',
//                 ],
//                 [
//                     'label' => 'Asporto',
//                     'data' => $allDates->map(fn($date) => isset($ordersAsporto[$date]) ? (int)$ordersAsporto[$date]['count'] : 0)->values()->toArray(),
//                     'borderColor' => '#10b7937b',
//                     'backgroundColor' => '#10b7937b',
//                 ],
//             ],
//         ];
//         $adv_s = Setting::where('name', 'advanced')->first();
//         if($adv_s){
//             $property_adv = json_decode($adv_s->property, 1);  
//         }else{
//             dump('Non sono state trovate le impostazioni avanzate');
//             dump('Per favore, imposta le opzioni avanzate nella pagina delle impostazioni o contatta l assistenza tecnica.');
//             dd('Error: Impostazioni avanzate non trovate');  
//         }
//         $notify = [];
//         $dates = Date::select('*') // o specifica i campi che ti servono
//         ->selectRaw("
//             STR_TO_DATE(
//                 CASE
//                     WHEN date_slot LIKE '%null%' THEN REPLACE(date_slot, ' null', ' 00:00')
//                     ELSE date_slot
//                 END,
//                 '%d/%m/%Y %H:%i'
//             ) AS order_slot
//         ")
//         ->orderBy('order_slot')
//         ->get();
//         if(count($dates) == 0){
//             return view('admin.dashboard', compact('setting', 'stat', 'product_', 'traguard', 'order', 'reservation', 'post', 'chartData', 'notify','adv_s'));
//         };
//         // creo calendario - meglio inizializzare con chiave 0 per semplicità
//         $year = [];
//         $year[] = [
//             'year' => $dates[0]['year'],
//             'month'=> $dates[0]['month'],
//             'days'=> [],
//         ];

//         // Recupera configurazioni
//         $double = $property_adv['dt'];
//         $pack = $property_adv['services'];
//         $type = $property_adv['too'];

//         $firstDay = [
//             'year' => $dates[0]['year'],
//             'month' => $dates[0]['month'],
//             'day' => $dates[0]['day'],
//         ];

//         foreach ($dates as $d) {
//             list($date, $time) = explode(" ", $d['date_slot']);

//             // assicuriamoci di avere l'indice corrente corretto
//             $cy = array_key_last($year);

//             // crea Carbon per calcolare giorni mancanti
//             $d1 = Carbon::create($firstDay['year'], $firstDay['month'], $firstDay['day']);
//             $d2 = Carbon::create($d['year'], $d['month'], $d['day']);
//             if ($d1->gt($d2)) {
//                 [$d1, $d2] = [$d2, $d1];
//             }

//             $diffInDays = $d1->diffInDays($d2);

//             if ($diffInDays >= 1) {
//                 // inserisco i giorni mancanti (dal giorno successivo di d1 fino a d2-1)
//                 $current = $d1->copy()->addDay();
//                 for ($i = 1; $i < $diffInDays; $i++) {
//                     // aggiorna indice corrente (potrebbe essere cambiato nei loop precedenti)
//                     $cy = array_key_last($year);

//                     // se il mese del giorno corrente non corrisponde al mese dell'array corrente, crea nuovo mese
//                     if ($current->month !== $year[$cy]['month'] || $current->year !== $year[$cy]['year']) {
//                         $year[] = [
//                             'year' => $current->year,
//                             'month' => $current->month,
//                             'days' => [],
//                         ];
//                         $cy = array_key_last($year);
//                     }

//                     // push del giorno mancante
//                     $year[$cy]['days'][] = [
//                         'day'   => $current->day,
//                         'day_w' => $current->dayOfWeekIso,
//                         'date'  => $current->format('d/m/Y'),
//                     ];

//                     $current->addDay();
//                 }

//                 // imposto firstDay all'ultimo giorno aggiunto (cioè d2-1)
//                 $lastAdded = $d2->copy()->subDay();
//                 $firstDay = [
//                     'year'  => $lastAdded->year,
//                     'month' => $lastAdded->month,
//                     'day'   => $lastAdded->day,
//                 ];
//             }

//             // costruisco la struttura del day (coerente anche se reserving == '0')
//             $day = [
//                 'day'   => $d['day'],
//                 'day_w' => $d['day_w'],
//                 'date'  => $date,
//                 'time'  => [],
//             ];

//             if ($d['reserving'] !== '0') {
//                 $res = json_decode($d['reserving'], true);
//                 if ($pack == 2) {
//                     $table = $double ? ($res['table_1'] + $res['table_2']) : ($res['table'] ?? 0);
//                     $day['table'] = $table;
//                     $time = ['time' => $d['time'], 'table' => $table];
//                 } elseif ($pack == 3) {
//                     $asporto = $type ? ($res['cucina_1'] + $res['cucina_2']) : ($res['asporto'] ?? 0);
//                     $domicilio = $res['domicilio'] ?? 0;
//                     $day['asporto'] = $asporto;
//                     $day['domicilio'] = $domicilio;
//                     $time = ['time' => $d['time'], 'asporto' => $asporto, 'domicilio' => $domicilio];
//                 } elseif ($pack == 4) {
//                     $asporto = $type ? ($res['cucina_1'] + $res['cucina_2']) : ($res['asporto'] ?? 0);
//                     $domicilio = $res['domicilio'] ?? 0;
//                     $table = $double ? ($res['table_1'] + $res['table_2']) : ($res['table'] ?? 0);
//                     $day['asporto'] = $asporto;
//                     $day['domicilio'] = $domicilio;
//                     $day['table'] = $table;
//                     $time = ['time' => $d['time'], 'asporto' => $asporto, 'domicilio' => $domicilio, 'table' => $table];
//                 } else {
//                     $time = ['time' => $d['time']];
//                 }
//             } else {
//                 // no reserving
//                 $time = ['time' => $d['time']];
//             }

//             // aggiorno cy (ultima chiave)
//             $cy = array_key_last($year);

//             // se il giorno appartiene al mese corrente (pari a firstDay)
//             if ($d['year'] == $firstDay['year'] && $d['month'] == $firstDay['month']) {
//                 // caso time == 0 -> giorno off
//                 if ($d['time'] == 0) {
//                     $year[$cy]['days'][] = [
//                         'day' => $d['day'],
//                         'day_w' => $d['day_w'],
//                         'date' => $date
//                     ];
//                 } else {
//                     // verifica se devo aggregare sul giorno esistente (stesso giorno) o creare nuova entry
//                     $lastDayIndex = count($year[$cy]['days']) - 1;
//                     $needNewDay = true;
//                     if ($lastDayIndex >= 0) {
//                         $lastDay = $year[$cy]['days'][$lastDayIndex];
//                         if ($lastDay['day'] == $d['day']) {
//                             // aggrego i valori sullo stesso giorno
//                             $needNewDay = false;
//                             // sommo i campi in base al pack
//                             if ($pack == 2 && isset($day['table'])) {
//                                 $year[$cy]['days'][$lastDayIndex]['table'] = ($year[$cy]['days'][$lastDayIndex]['table'] ?? 0) + $day['table'];
//                             } elseif ($pack == 3) {
//                                 $year[$cy]['days'][$lastDayIndex]['asporto'] = ($year[$cy]['days'][$lastDayIndex]['asporto'] ?? 0) + ($day['asporto'] ?? 0);
//                                 $year[$cy]['days'][$lastDayIndex]['domicilio'] = ($year[$cy]['days'][$lastDayIndex]['domicilio'] ?? 0) + ($day['domicilio'] ?? 0);
//                             } elseif ($pack == 4) {
//                                 $year[$cy]['days'][$lastDayIndex]['table'] = ($year[$cy]['days'][$lastDayIndex]['table'] ?? 0) + ($day['table'] ?? 0);
//                                 $year[$cy]['days'][$lastDayIndex]['domicilio'] = ($year[$cy]['days'][$lastDayIndex]['domicilio'] ?? 0) + ($day['domicilio'] ?? 0);
//                                 $year[$cy]['days'][$lastDayIndex]['asporto'] = ($year[$cy]['days'][$lastDayIndex]['asporto'] ?? 0) + ($day['asporto'] ?? 0);
//                             }
//                             // aggiungo l'orario al tempo
//                             $year[$cy]['days'][$lastDayIndex]['time'][] = $time;
//                         }
//                     }

//                     if ($needNewDay) {
//                         // creo nuova entry giorno completa
//                         $dayToPush = $day;
//                         $dayToPush['time'] = [$time];
//                         $year[$cy]['days'][] = $dayToPush;
//                     }
//                 }
//             } else {
//                 // mese differente -> creo nuovo mese e aggiungo il giorno
//                 $month = [
//                     'year' => $d['year'],
//                     'month' => $d['month'],
//                     'days' => []
//                 ];
//                 if ($d['time'] == 0) {
//                     $month['days'][] = [
//                         'day' => $d['day'],
//                         'day_w' => $d['day_w'],
//                         'date' => $date
//                     ];
//                 } else {
//                     $day['time'] = [$time];
//                     $month['days'][] = $day;
//                 }
//                 $year[] = $month;
//             }

//             // imposto firstDay al record corrente (per iterazione successiva)
//             $firstDay = [
//                 'year'  => $d['year'],
//                 'month' => $d['month'],
//                 'day'   => $d['day'],
//             ];
//         }

//         // Ottieni ordini non notificati
//         $not_or = Order::where('notificated', 0)->where('status', '!=', 4)->get();
//         $not_res = Reservation::where('notificated', 0)->where('status', '!=', 4)->get();
//         //fine date
//         //cerco notifiche
//         if (count($not_or) || count($not_res)) {
//             if (count($not_or)){
//                 foreach ($not_or as $o) {
//                     $n = [
//                         'm' => 'È stato appena concluso un ordine: da ' . $o->name . ' per il ' . $o->date_slot . ' di €' . $o->tot_price / 100,
//                         'type' => 'or',
//                         'id' => $o->id
//                     ]; 
//                     array_push($notify, $n); $o->notificated = 1; $o->update();
//                 }
//             }
//             if (count($not_res)){
//                 foreach ($not_res as $o) {
//                     $person = json_decode($o->n_person, 1);
//                     $n = [
//                         'm' => 'È stata appena conclusa una prenotazione: da ' . $o->name . ' per il ' . $o->date_slot . ' , gli ospiti sono ' . $person['adult'].' adulti e '.$person['child'].' bambini.',
//                         'type' => 'res',
//                         'id' => $o->id
//                     ];
//                     array_push($notify, $n); $o->notificated = 1; $o->update();
//                 }
//             }
//         }
//         return view ('admin.dashboard', compact('year', 'setting', 'stat', 'product_', 'traguard', 'order', 'reservation', 'post', 'chartData', 'notify','adv_s'));
//        // dd($year);
//     }