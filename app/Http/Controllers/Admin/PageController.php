<?php



namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Date;
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
    public function dashboard() {

        $setting = Setting::all();
        $product_ = [
            1 => Product::where('visible', 1)->where('archived', 0)->count(),
            2 => Product::where('archived', 1)->count(),
        ];
        $stat = [
            1 => Category::count(),
            2 => Ingredient::count(),
        ];
        $meseCorrenteInizio = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
        $meseCorrenteFine = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
        $traguard = [
            1 =>  Order::whereBetween(DB::raw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')"), [$meseCorrenteInizio, $meseCorrenteFine])->sum('tot_price'),
            2 =>  Order::sum('tot_price'),
            3 =>  Reservation::selectRaw('SUM(JSON_EXTRACT(n_person, "$.adult") + JSON_EXTRACT(n_person, "$.child")) AS total_persons')
                ->whereBetween(DB::raw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')"), [
                    Carbon::now()->startOfMonth()->format('Y-m-d H:i:s'),
                    Carbon::now()->endOfMonth()->format('Y-m-d H:i:s')
                ])
                ->value('total_persons'),
            4 =>  Reservation::selectRaw('SUM(JSON_EXTRACT(n_person, "$.adult") + JSON_EXTRACT(n_person, "$.child")) AS total_persons')
                ->whereBetween(DB::raw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')"), [
                    Carbon::now()->startOfYear()->format('Y-m-d H:i:s'),
                    Carbon::now()->endOfYear()->format('Y-m-d H:i:s')
                ])
                ->value('total_persons')
        ];
        $post = [ 
            1 => Post::count(),
            2 => Post::where('visible', 0)->count(),
            3 => Post::where('visible', 1)->where('archived', 0)->count(),
            4 => Post::where('archived', 1)->count(),
        ];
        $order = [ 
            1 => Order::where('status', 1)->count() + Order::where('status', 5)->count(),
            2 => Order::where('status', 2)->count(),
            3 => Order::where('status', 0)->count() + Order::where('status', 6)->count(),
            4 => Order::where('status', 3)->count(),
        ];
        $reservation = [
            1 => Reservation::where('status', 1)->count() + Reservation::where('status', 5)->count(),
            2 => Reservation::where('status', 2)->count(),
            3 => Reservation::where('status', 0)->count(),
            4 => Reservation::where('status', 3)->count(),
        ];
        // Fetch and group Reservations by date
        $reservations = Reservation::selectRaw("DATE_FORMAT(STR_TO_DATE(`date_slot`, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderByRaw("STR_TO_DATE(date, '%Y-%m-%d')")
            ->get()
            ->keyBy('date');

        // Fetch and group Orders by date for delivery and asporto
        $ordersDelivery = Order::whereNotNull('comune')
            ->selectRaw("DATE_FORMAT(STR_TO_DATE(`date_slot`, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderByRaw("STR_TO_DATE(date, '%Y-%m-%d')")
            ->get()
            ->keyBy('date');

        $ordersAsporto = Order::whereNull('comune')
            ->selectRaw("DATE_FORMAT(STR_TO_DATE(`date_slot`, '%d/%m/%Y %H:%i'), '%Y-%m-%d') as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderByRaw("STR_TO_DATE(date, '%Y-%m-%d')")
            ->get()
            ->keyBy('date');

        // Combine all dates for consistency in the chart
        $allDates = collect(array_merge(
            $reservations->keys()->toArray(),
            $ordersDelivery->keys()->toArray(),
            $ordersAsporto->keys()->toArray()
        ))->unique()->sort();

        // Prepare the data for the chart
       // Prepare the data for the chart

        $chartData = [
            'labels' => $allDates->map(fn($date) => Carbon::parse($date))->values()->toArray(),
            'datasets' => [
                [
                    'label' => 'Prenotazioni',
                    'data' => $allDates->map(fn($date) => isset($reservations[$date]) ? (int)$reservations[$date]['count'] : 0)->values()->toArray(),
                    
                    'borderColor' => '#090333',
                    'backgroundColor' => '#090333',
                ],
                [
                    'label' => ' Delivery',
                    'data' => $allDates->map(fn($date) => isset($ordersDelivery[$date]) ? (int)$ordersDelivery[$date]['count'] : 0)->values()->toArray(),
                    'borderColor' => '#10b793',
                    'backgroundColor' => '#10b793',
                ],
                [
                    'label' => 'Asporto',
                    'data' => $allDates->map(fn($date) => isset($ordersAsporto[$date]) ? (int)$ordersAsporto[$date]['count'] : 0)->values()->toArray(),
                    'borderColor' => '#10b7937b',
                    'backgroundColor' => '#10b7937b',
                ],
            ],
        ];
        $adv_s = Setting::where('name', 'advanced')->first();
        if(!$adv_s){
            $setting = new Setting();
            $setting->name = 'advanced';
            $property_adv = [
                'too' => false,
                'dt' => false,
                'services' => '4', //1 niente // 2 tavoli // 3 asporto // 4 tutti
                
                'menu_fix_set' => '1',
                'too_1' => 'pizza',
                'too_2' => 'fritti',
                'sala_1' => 'Sala Sushi',
                'sala_2' => 'Sala ITA',
                'p_iva' => '',
                'r_sociale' => '',
                'times_start' => '11:20',
                'times_end' => '22:20',
                'max_day_res' => '20',
                'times_interval' => 20,
                'c_rea' => '',
                'c_sociale' => '',
                'c_ateco' => '',
                'u_imprese' => '',
                'method' => [],
                'set_time'=> [
                    'tavoli',
                    'asporto',
                    'domicilio',
                ]
            ];
            $setting->property = json_encode($property_adv);
            $setting->save();
            $adv_s = $setting;
        }else{
            $property_adv = json_decode($adv_s->property, 1);  
        }
        $notify = [];
        $dates = Date::select('*') // o specifica i campi che ti servono
        ->selectRaw("
            STR_TO_DATE(
                CASE
                    WHEN date_slot LIKE '%null%' THEN REPLACE(date_slot, ' null', ' 00:00')
                    ELSE date_slot
                END,
                '%d/%m/%Y %H:%i'
            ) AS order_slot
        ")
        ->orderBy('order_slot')
        ->get();
        if(count($dates) == 0){
            return view('admin.dashboard', compact('setting', 'stat', 'product_', 'traguard', 'order', 'reservation', 'post', 'chartData', 'notify','adv_s'));
        };
        // creo calendario 
        $year = [1 => ['year' => $dates[0]['year'],'month'=> $dates[0]['month'],'days'=> [],]];

        // Recupera configurazioni
        $double = $property_adv['dt'];
        $pack = $property_adv['services'];
        $type = $property_adv['too'];

        
        $firstDay = [ 'year' => $dates[0]['year'], 'month' => $dates[0]['month'], 'day' => $dates[0]['day'],];       
        
        foreach ($dates as $d) {
            list($date, $time) = explode(" ", $d['date_slot']);
            $cy = count($year);
           
            
            $d1 = Carbon::create($firstDay['year'], $firstDay['month'], $firstDay['day']);
            $d2 = Carbon::create($d['year'], $d['month'], $d['day']);
            if ($d1->gt($d2)) {
                [$d1, $d2] = [$d2, $d1];
            }
            $diffInDays = $d1->diffInDays($d2);
            if ($diffInDays >= 1) {
                // Altrimenti crea array di giorni mancanti
                $current = $d1->copy()->addDay();

                for ($i = 1; $i < $diffInDays; $i++) {
                    array_push($year[$cy]['days'], [
                        'day' => $current->day,
                        'day_w' => $current->dayOfWeekIso,
                        'date' => $current->format('d/m/Y')]);
                        
                    if($current->isLastOfMonth()){
                        $current->addDay();
                        array_push($year, [
                            'year' =>  $current->year,
                            'month' => $current->month,
                            'days' => [],
                        ]);
                        //dump($diffInDays);
                        $cy++;
                    }else{
                        $current->addDay();
                    }
                    //dump($current->day);
                }
                $current->subDay(); // Rimuove il giorno corrente per evitare duplicati
                $firstDay = [
                    'year' =>  $current->year,
                    'month' =>  $current->month,
                    'day' =>  $current->day,
                ];
            }
            if($d['reserving'] !== '0'){
                $res = json_decode($d['reserving'], 1);
                // Prepara base comune per $day
                $day = [
                    'day' => $d['day'],
                    'day_w' => $d['day_w'],
                    'date' => $date,
                    'time' => [],
                ];
                // Prepara base comune per $time
                $time = [
                    'time' => $d['time'],
                ];
                if ($pack == 2) {
                    $table = $double ? $res['table_1'] + $res['table_2'] : $res['table'];
                    $day['table'] = $table;
                    $time['table'] = $table;
                } elseif ($pack == 3) {
                    $asporto = $type 
                    ? ($res['cucina_1'] + $res['cucina_2']) 
                    : ($res['asporto'] ?? null);
                    $domicilio = $res['domicilio'] ?? null;
                    $day['asporto'] = $asporto;
                    $day['domicilio'] = $domicilio;
                    $time['asporto'] = $asporto;
                    $time['domicilio'] = $domicilio;
                } elseif ($pack == 4) {
                    $asporto = $type 
                    ? ($res['cucina_1'] + $res['cucina_2']) 
                    : ($res['asporto'] ?? null);
                    $domicilio = $res['domicilio'] ?? null;
                    $table = $double ? $res['table_1'] + $res['table_2'] : $res['table'];
                    $day['asporto'] = $asporto;
                    $day['domicilio'] = $domicilio;
                    $day['table'] = $table;
                }

            }
            $cd = count($year[$cy]['days']) - 1;
            if($d['year'] == $firstDay['year'] && $d['month'] == $firstDay['month']){
                
                
                if( $d['time'] == 0 ){
                    array_push($year[$cy]['days'], $dayoff = [
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date]);
                }elseif($d['day'] !== $firstDay['day'] || count($year[1]['days']) == 0){
                    array_push($year[$cy]['days'], $day);
                    $cd ++;
                    array_push($year[$cy]['days'][$cd]['time'], $time);
                }elseif($d['day'] == $firstDay['day']){
                    if( $pack == 2 ){        
                        $year[$cy]['days'][$cd]['table'] += $day['table'];        
                    }elseif( $pack == 3){
                        $year[$cy]['days'][$cd]['asporto'] += $day['asporto'];
                        $year[$cy]['days'][$cd]['domicilio'] += $day['domicilio'];
                    }elseif( $pack == 4){
                        $year[$cy]['days'][$cd]['table'] += $day['table'];
                        $year[$cy]['days'][$cd]['domicilio'] += $day['domicilio'];
                        $year[$cy]['days'][$cd]['asporto'] += $day['asporto'];
                        
                    }
                    array_push($year[$cy]['days'][count($year[$cy]['days']) - 1]['time'], $time);
                }
               
            }else{ 
                $month = [
                    'year' =>  $d['year'],
                    'month' => $d['month'],
                    'days' => [],
                ];
                if($d['reserving'] !== '0'){
                    array_push($month['days'], $day);
                }else{
                    array_push($month['days'], $dayoff = [
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date]);
                }
                array_push($year, $month);
            }
        
            $firstDay = [
                'year' =>  $d['year'],
                'month' =>  $d['month'],
                'day' =>  $d['day'],
            ];
           
            
        };
        // Ottieni ordini non notificati
        $not_or = Order::where('notificated', 0)->where('status', '!=', 4)->get();
        $not_res = Reservation::where('notificated', 0)->where('status', '!=', 4)->get();
        //fine date
        //cerco notifiche
        if (count($not_or) || count($not_res)) {
            if (count($not_or)){
                foreach ($not_or as $o) {
                    $n = [
                        'm' => 'È stato appena concluso un ordine: da ' . $o->name . ' per il ' . $o->date_slot . ' di €' . $o->tot_price / 100,
                        'type' => 'or',
                        'id' => $o->id
                    ]; 
                    array_push($notify, $n); $o->notificated = 1; $o->update();
                }
            }
            if (count($not_res)){
                foreach ($not_res as $o) {
                    $person = json_decode($o->n_person, 1);
                    $n = [
                        'm' => 'È stata appena conclusa una prenotazione: da ' . $o->name . ' per il ' . $o->date_slot . ' , gli ospiti sono ' . $person['adult'].' adulti e '.$person['child'].' bambini.',
                        'type' => 'res',
                        'id' => $o->id
                    ];
                    array_push($notify, $n); $o->notificated = 1; $o->update();
                }
            }
        }
        return view ('admin.dashboard', compact('year', 'setting', 'stat', 'product_', 'traguard', 'order', 'reservation', 'post', 'chartData', 'notify','adv_s'));
       // dd($year);
    }

    function getMissingDays($date1, $date2, ) {
        // Crea oggetti Carbon dalle date
        

        return $missingDays;
    }

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
}

