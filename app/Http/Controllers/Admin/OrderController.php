<?php

namespace App\Http\Controllers\Admin;

use DateTime;
use Carbon\Carbon;
use Stripe\Refund;
use Stripe\Stripe;
use App\Models\Date;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Ingredient;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    

    public function filter(Request $request){
        
        // FUNZIONE DI FILTRAGGIO INDEX
        $status = $request->input('status');
        $name = $request->input('name');
        $order = $request->input('order');
        $date = $request->input('date');

        $filters = [
            'name'          => $name ,
            'status'        => $status ,
            'date'          => $date ,
            'order'         => $order,     
        ];
        //dd($date);
        $query = Order::query();
       
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%')
            ->orWhere('surname', 'like', '%' . $name . '%');
        } 
        if ($status == 4) {
            $query->where('status', 0)
            ->orWhere('status', 6);
        } else if ($status == 1) {
            $query->where('status', 1)
            ->orWhere('status', 5);
        } else if ($status == 2) {
            $query->where('status', 2)
            ->orWhere('status', 3);
        } else if ($status == 5) {
            $query->where('status', 3)
            ->orWhere('status', 5);
        }else{ 
            $query->where('status', '!=', 4);
        }
        if($date){
            $formattedDate = DateTime::createFromFormat('Y-m-d', $date)->format('d/m/Y');

            $query->where('date_slot', 'like', '%' . $formattedDate . '%');
        }
        if($order){
            $orders = $query->orderBy('date_slot', 'asc')->get();
        }else{
            $orders = $query->orderBy('created_at', 'asc')->get();    
        }        
    

        $data = [];
        array_push($data, $filters);
        array_push($data, $orders);

        return redirect()->back()->with('filter', $data);
    }

    protected function statusF( $c_a, $id){

        $order = Order::where('id', $id)->with('products')->firstOrFail();
        $message = '';
        if($c_a){
            if($order->status == 2 || $order->status == 0){
                $order->status = 1;
            }elseif($order->status == 3){
                $order->status = 5;
            }
            $m = 'L\'ordine è stata confermato correttamente';
            $message = 'Grazie ' . $order->name . ' per aver ordinato da noi, ti confermiamo che il tuo ordine sarà pronto per il ' . $order->date_slot;    
        }else{
            if(in_array($order->status, [3, 5])){
                $m = 'L\'ordine è stato annullato e RIMBORSATO correttamente';
                $message = 'Ci dispiace informarti che purtroppo il tuo ordine è stato annullato e rimborsato';
                //codice per rimborso
                try {
                    $stripeSecretKey = config('configurazione.STRIPE_SECRET'); 
                 
                    // Imposta la chiave segreta di Stripe
                    Stripe::setApiKey($stripeSecretKey);
        
                    if ($order->checkout_session_id === null) {
                        return response()->json(['error' => 'Payment not found'], 404);
                    }
        
                    // Effettua il rimborso
                    $refund = Refund::create([
                        'payment_intent' => $order->checkout_session_id, // Questo è l'ID dell'intent di pagamento
                    ]);
        
                    // Aggiorna lo stato del rimborso nella tua tabella
                    $order->status = 6;
        
                    
                } catch (\Exception $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }
                
            }elseif(in_array($order->status, [2, 1])){
                $m = 'L\'ordine è stato annullato correttamente';
                $message = 'Ci dispiace informarti che purtroppo il tuo ordine è stato annullato';
                $order->status = 0;
            }else{
                $m = 'L\'ordine era gia stato annullato!';
                return redirect()->back()->with('success', $m); 
            }            
            
        }
        $order->update();
        //new menu
        $product_r = [];
        foreach ($order->products as $p) {
            $arrO = $p->pivot->option !== '[]' ? json_decode($p->pivot->option, true) : [];
            $arrA = $p->pivot->add !== '[]' ? json_decode($p->pivot->add, true) : [];
            $r_option = [];
            $r_add = [];
            foreach ($arrO as $o) {
                $ingredient = Ingredient::where('name', $o)->first();
                $r_option[] = $ingredient;
            }
            foreach ($arrA as $o) {
                $ingredient = Ingredient::where('name', $o)->first();
                $r_add[] = $ingredient;
            }
            $p->setAttribute('r_option', $r_option);
            $p->setAttribute('r_add', $r_add);
            $product_r[] = $p;
        }
        $cart_mail = [
            'products' => $product_r,
            'menus' => $order->menus,
        ];
        //new menu
        $cart_price = 0;
        $delivery_cost = 0;
        if($order->comune){
            foreach ($order->products as $o) {
                $add = json_decode( $o->pivot->add , 1);
                $option = json_decode( $o->pivot->option , 1);
                foreach ($add as $a) {
                    $ing = Ingredient::where('name', $a)->first();
                    $cart_price += $ing->price * $o->pivot->quantity;
                }
                foreach ($option as $a) {
                    $ing = Ingredient::where('name', $a)->first();
                    $cart_price += $ing->price * $o->pivot->quantity;
                }
                $cart_price += $o->price * $o->pivot->quantity;
            }
            foreach ($order->menus as $menu) {
                $cart_price += $menu->price * ($menu->pivot->quantity ? $menu->pivot->quantity : 1);
                if($menu->fixed_menu == '2'){
                    foreach ($menu->products as $p) {  
                        if(in_array($p->id, array_column($menu->products, 'id'))){
                            $cart_price += $p->pivot->extra_price * ($menu->pivot->quantity > 0 ? $menu->pivot->quantity : 1);
                        } 
                    }
                }
            }
            $delivery_cost = $order->tot_price - $cart_price;
        }
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);
        $set = Setting::where('name', 'Contatti')->first();
        $p_set = json_decode($set->property, true);
        $bodymail = [
            'type' => 'or',
            'to' => 'user',
            'order_id' => $order->id,
            'name' => $order->name,
            'surname' => $order->surname,
            'email' => $order->email,
            'date_slot' => $order->date_slot,
            'message' => $order->message,
            'phone' => $order->phone,
            'admin_phone' => $p_set['telefono'],
            
            'comune' => $order->comune,
            'address' => $order->address,
            'address_n' => $order->address_n,
            'delivery_cost' => $delivery_cost,
            
            'title' =>  $c_a ? 'Ti confermiamo che il tuo ordine è stato accettato' : 'Ci dispiace informarti che il tuo ordine è stato annullato',
            'subtitle' => $order->status == 6 ? 'Il tuo rimborso verrà elaborato in 5-10 gironi lavorativi' : '',
            'whatsapp_message_id' => $order->whatsapp_message_id,

            'status' => $order->status,
            'cart' => $cart_mail,
            'total_price' => $order->tot_price,

            'property_adv' => $property_adv,
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($order['email'])->send($mail);

       
        
        return [
            'm' => $m,
            'message' => $message,
        ];
    }

    public function status(Request $request){
        $wa = $request->input('wa');
        $c_a = $request->input('c_a');
        $id = $request->input('id');
        
        $m = $this->statusF( $c_a, $id);

        if($wa){
            return redirect("https://wa.me/39" . $order->phone . "?text=" . $m['message']);
        }
        
        return redirect()->back()->with('success', $m['m']);   
    }
    public function status_mail(){
        $id = request()->query('id');
        $c_a = request()->query('c_a');

        $this->statusF( $c_a, $id);
    }

    public function index()
    {
        $order_remove = Order::where('status', 4)
            ->where('created_at', '<', Carbon::now()->subHours(2))
            ->get();
        if (!$order_remove->isEmpty()) {
            foreach ($order_remove as $k) {
                $k->menus()->detach(); // se hai la relazione many-to-many definita
                // Poi elimina l'ordine
                $k->delete();
            }
        }

        $orders = Order::orderBy('date_slot', 'asc')->get();
        return view('admin.Orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::where('id', $id)->with('products', 'menus.products.category')->firstOrFail();
        $cart_price = 0;
        $delivery_cost = 0;
        foreach ($order->products as $o) {
            $add = json_decode( $o->pivot->add , 1);
            $option = json_decode( $o->pivot->option , 1);
            foreach ($add as $a) {
                $ing = Ingredient::where('name', $a)->first();
                $cart_price += $ing->price * $o->pivot->quantity;
            }
            foreach ($option as $a) {
                $ing = Ingredient::where('name', $a)->first();
                $cart_price += $ing->price * $o->pivot->quantity;
            }
            $cart_price += $o->price * $o->pivot->quantity;
        }
        foreach ($order->menus as $menu) {
            $cart_price += $menu->price * $menu->pivot->quantity;
            if($menu->fixed_menu == '2'){
                $choices = json_decode($menu->pivot->choices, 1);
                foreach ($choices as $p) {  
                    foreach ($menu->products as $diocane) {
                        if($diocane->id == $p){
                            $cart_price += $diocane->pivot->extra_price * $menu->pivot->quantity;
                            break;
                        }
                    }
                }
            }
        }
        $delivery_cost = $order->tot_price - $cart_price;

        $times_start = '01:00';
        $times_end = '23:59';
        $times_interval = 30;

        $adv_setting = Setting::where('name', 'advanced')->first();
        if ($adv_setting) {
            $adv = json_decode($adv_setting->property, true);
            $times_start = $adv['times_start'] ?? $times_start;
            $times_end = $adv['times_end'] ?? $times_end;
            $times_interval = intval($adv['times_interval'] ?? $times_interval);
        }

        return view('admin.Orders.show', compact('order', 'delivery_cost', 'times_start', 'times_end', 'times_interval'));
    }

    public function destroy($id)
    {
        //
    }
    
    public function changetime(Request $request){
       // prima parte

        $order = Order::where('id', $request['id'])->with('products')->first();

        // Logica per time_blocked: blocca il vecchio slot
        $date_slot_parts = explode(' ', $order->date_slot);
        $old_date = $date_slot_parts[0]; // dd/mm/yyyy
        $old_time = $date_slot_parts[1]; // hh:mm

        $date_formatted = DateTime::createFromFormat('d/m/Y', $old_date)->format('Y-m-d');

        $adv_setting = Setting::where('name', 'advanced')->first();
        $adv = json_decode($adv_setting->property, true);

        if (!isset($adv['time_blocked'])) {
            $adv['time_blocked'] = [];
        }

        if ($request['block']) {
            $adv['time_blocked'][$date_formatted][] = $old_time;
        }

        // Rimuovi giorni precedenti alla data odierna
        $today = Carbon::today()->format('Y-m-d');
        foreach ($adv['time_blocked'] as $day => $times) {
            if ($day < $today) {
                unset($adv['time_blocked'][$day]);
            }
        }

        $adv_setting->property = json_encode($adv);
        $adv_setting->update();

        $new_time = $request['new_time'];
        $content_notify = __('admin.order_postponed', ['time' => $new_time]);
        
        // Aggiorna il date_slot con il nuovo orario
        $order->date_slot = $old_date . ' ' . $new_time;

        $ship = $order->comune ? __('admin.ship_deliver') : __('admin.ship_prepare');
        $ship_2 = $order->comune ? __('admin.ship2_delivery') : __('admin.ship2_pickup');
        $message_wa = __('admin.order_changed_message', ['name' => $order->name, 'ship2' => $ship_2, 'ship' => $ship, 'time' => $new_time]);
        $sub = __('admin.order_changed_subtitle', ['ship2' => $ship_2, 'ship' => $ship, 'time' => $new_time]);
        

        $order->status = $order->status == 3 ? 5 : 1;
        $order->update();

        //seconda parte
        $product_r = [];
        foreach ($order->products as $p) {
            $arrO = $p->pivot->option !== '[]' ? json_decode($p->pivot->option, true) : [];
            $arrA = $p->pivot->add !== '[]' ? json_decode($p->pivot->add, true) : [];
            $r_option = [];
            $r_add = [];
            foreach ($arrO as $o) {
                $ingredient = Ingredient::where('name', $o)->first();
                $r_option[] = $ingredient;
            }
            foreach ($arrA as $o) {
                $ingredient = Ingredient::where('name', $o)->first();
                $r_add[] = $ingredient;
            }
            $p->setAttribute('r_option', $r_option);
            $p->setAttribute('r_add', $r_add);
            $product_r[] = $p;
        }
        $cart_mail = [
            'products' => $product_r,
            'menus' => $order->menus,
        ];
        //new menu end
        $cart_price = 0;
        $delivery_cost = 0;
        if($order->comune){
            foreach ($order->products as $o) {
                $add = json_decode( $o->pivot->add , 1);
                $option = json_decode( $o->pivot->option , 1);
                foreach ($add as $a) {
                    $ing = Ingredient::where('name', $a)->first();
                    $cart_price += $ing->price * $o->pivot->quantity;
                }
                foreach ($option as $a) {
                    $ing = Ingredient::where('name', $a)->first();
                    $cart_price += $ing->price * $o->pivot->quantity;
                }
                $cart_price += $o->price * $o->pivot->quantity;
            }
            foreach ($order->menus as $menu) {
                $cart_price += $menu->price * ($menu->pivot->quantity ? $menu->pivot->quantity : 1);
                if($menu->fixed_menu == '2'){
                    foreach ($menu->products as $p) {  
                        if(in_array($p->id, array_column($menu->products, 'id'))){
                            $cart_price += $p->pivot->extra_price * ($menu->pivot->quantity > 0 ? $menu->pivot->quantity : 1);
                        } 
                    }
                }
            }
            $delivery_cost = $order->tot_price - $cart_price;
        }

        $set = Setting::where('name', 'Contatti')->firstOrFail();
        $p_set = json_decode($set->property, true);
        $bodymail = [
            'type' => 'or',
            'to' => 'user',
            'order_id' => $order->id,
            'name' => $order->name,
            'surname' => $order->surname,
            'email' => $order->email,
            'date_slot' => explode(' ', $order->date_slot)[0],
            'message' => $order->message,
            'phone' => $order->phone,
            'admin_phone' => $p_set['telefono'],
            
            'comune' => $order->comune,
            'address' => $order->address,
            'address_n' => $order->address_n,
            'delivery_cost' => $delivery_cost,

            'title' =>  __('admin.order_changed_title', ['name' => $order->name]),
            'subtitle' => $sub,
            'whatsapp_message_id' => $order->whatsapp_message_id,

            'status' => $order->status,
            'cart' => $cart_mail,
            'total_price' => $order->tot_price,
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($order['email'])->send($mail);

        return redirect()->back()->with('success', $content_notify);
    }
}
