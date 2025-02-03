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
        

        $post = [ 
            1 => Post::count(),
            2 => Post::where('visible', 0)->count(),
            3 => Post::where('visible', 1)->where('archived', 0)->count(),
            4 => Post::where('archived', 1)->count(),
        ];


       // dd($year);
        return view ('admin.dashboard', compact( 'post'));
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
            //dump('ciao');
            // /dump($order);
            // dump($order->status);
            // dump($order->date);

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

