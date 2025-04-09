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

    protected function statusF($wa, $c_a, $id){

        $order = Order::where('id', $id)->with('products')->firstOrFail();
        $date = Date::where('date_slot', $order->date_slot)->first();
            if($date == null){
                return 'error data';
            }
        //dd($order);
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
                $m = 'L\'ordine è stata annullato e RIMBORSATO correttamente';
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
                $m = 'L\'ordine è stata annullato correttamente';
                $message = 'Ci dispiace informarti che purtroppo il tuo ordine è stato annullato';
                $order->status = 0;
            }else{
                $m = 'L\'ordine era gia stato annullato!';
                return redirect()->back()->with('success', $m); 
            }
            
            $vis = json_decode($date->visible, 1); 
            $reserving = json_decode($date->reserving, 1);
            if(config('configurazione.typeOfOrdering')){
                $np_cucina_1 = 0;
                $np_cucina_2 = 0;
                foreach ($order->products as $p) {
                    $qt = 0;
                    $op = OrderProduct::where('product_id', $p->id)->where('order_id', $order->id)->first();
                    if($op !== null){
                        $qt = $op->quantity;
                        if($p->type_plate == 1 && $qt !== 0){
                            $np_cucina_1 += ($p->slot_plate * $qt);
                            if($vis['cucina_1'] == 0){
                                $vis['cucina_1'] = 1;
                            }
                        }
                        if($p->type_plate == 2){
                            $np_cucina_2 += ($p->slot_plate * $qt);
                            if($vis['cucina_2'] == 0){
                                $vis['cucina_2'] = 1;
                            }
                        }
                    }
                }
                $reserving['cucina_1'] = $reserving['cucina_1'] - $np_cucina_1;
                $reserving['cucina_2'] = $reserving['cucina_2'] - $np_cucina_2;
                if($order->address !== null){
                    if($vis['domicilio'] == 0){
                        $vis['domicilio'] = 1;
                    }
                    $reserving['domicilio'] --;
                }
            }else{
                if($order->address !== null){
                    if($vis['domicilio'] == 0){
                        $vis['domicilio'] = 1;
                    }
                    if($vis['asporto'] == 0){
                        $vis['asporto'] = 1;
                    }
                    $reserving['domicilio'] --;
                }else{
                    if($vis['asporto'] == 0){
                        $vis['asporto'] = 1;
                    }
                    $reserving['asporto'] --;

                }
            }

            $date->reserving = json_encode($reserving);
            $date->visible = json_encode($vis);
            $date->update();

            
            
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
        $set = Setting::where('name', 'Contatti')->firstOrFail();
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
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($order['email'])->send($mail);

       
        if($wa){
            return redirect("https://wa.me/39" . $order->phone . "?text=" . $message);
        }
        return $m;
    }

    public function status(Request $request){
        $wa = $request->input('wa');
        $c_a = $request->input('c_a');
        $id = $request->input('id');
        
        $m = $this->statusF($wa, $c_a, $id);
        if($m == 'error data'){
            return redirect()->back()->with('error', 'ATTENZIONE! La data relativa alla prenotazione/ordine selezionata/o non è più esistente');
        }
        
        return redirect()->back()->with('success', $m);   
    }
    public function status_mail(){
        $id = request()->query('id');
        $c_a = request()->query('c_a');
        $wa = false;

        $this->statusF($wa, $c_a, $id);
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
        $query = Order::whereRaw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) >= ?", [now()->toDateString()]);

        $orders = $query->where('status', '!=', 4)->orderBy('date_slot', 'asc')->get();
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
                    $cart_price += Product::where('id', $p)->with('menus')->first()->pivot->extra_price * $menu->pivot->quantity;
                }
            }
        }
        $delivery_cost = $order->tot_price - $cart_price;
        

        return view('admin.Orders.show', compact('order', 'delivery_cost'));
    }

    public function destroy($id)
    {
        //
    }
    
    public function changetime(Request $request){
       // dd($request);

        $order = Order::where('id', $request['id'])->with('products')->first();

        $new_time = $request['new_time'];
        $content_notify = 'L\'ordine è stato posticipato correttamente alle: ' . $new_time;
        
        //dd($content_notify);
        $ship = $order->comune ? 'consegnare' : 'preparare';
        $ship_2 = $order->comune ? 'alla tua consegna' : 'al tuo ritiro';
        $message_wa = 'Ciao ' . $order->name . ' ti informiamo che abbiamo accettato il tuo ordine, al fine di offrirti il miglior servizio e la miglior qualità dei prodotti '. $ship_2 .', riusciremo a ' . $ship . ' l\'ordine entro questo quest\'orario: ' . $new_time ;
        $sub = 'Al fine di offrirti il miglior servizio e la miglior qualità dei prodotti '. $ship_2 .', riusciremo a ' . $ship . ' l\'ordine entro questo quest\'orario: ' . $new_time ;
        
        $date = Date::where('date_slot', $order->date_slot)->first();
        if($date == null){
            return redirect()->back()->with('error', 'La data relativa alla prenotazione/ordine selezionata/o non è più esistente');
        }
        $vis = json_decode($date->visible, 1); 
        $reserving = json_decode($date->reserving, 1);

        if($request['cancel'] == 0){
            $content_notify .= ' e l\'orario è stato bloccato';
            if(config('configurazione.typeOfOrdering')){
                $vis['cucina_1'] = 0;
                $vis['cucina_2'] = 0;
                $vis['domicilio'] = 0;
            }else{
                $vis['asporto'] = 0;
                $vis['domicilio'] = 0;
            }
        }


        $date->reserving = json_encode($reserving);
        $date->visible = json_encode($vis);

        $order->status = $order->status == 3 ? 5 : 1;
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

            'title' =>  'Ciao ' . $order->name . ' ti informiamo che il tuo ordine è stato confermato',
            'subtitle' => $sub,
            'whatsapp_message_id' => $order->whatsapp_message_id,

            'status' => $order->status,
            'cart' => $cart_mail,
            'total_price' => $order->tot_price,
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($order['email'])->send($mail);

       
        // if($content_notify){
        //     return redirect("https://wa.me/39" . $order->phone . "?text=" . $message_wa);
        // }
        return redirect()->back()->with('success', $content_notify);
    }
}
