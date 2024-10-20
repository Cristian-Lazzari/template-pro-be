<?php

namespace App\Http\Controllers\Api;

use App\Models\Date;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Ingredient;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use App\Events\NewOrderNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Api\PaymentController;



class OrderController extends Controller
{
    private $validations = [
        'name'      => 'required|string|max:50',
        'phone'     => 'required|string|max:20',
        'email'     => 'required|email|max:100',
        'message'   => 'nullable|string|max:1000',
    ];

    public function store(Request $request)
    { 
        
        $payment_controller = new PaymentController();

        $request->validate($this->validations);
        $delivery = false;
        $data = $request->all();
        try {

            $date = Date::where('date_slot', $data['date_slot'])->firstOrFail();
            $vis = json_decode($date->visible, true);
            $av = json_decode($date->availability, true);
            $res = json_decode($date->reserving, true);
    
            $arrvar = str_replace('\\', '', $data['cart']);
            $cart = json_decode($arrvar, true);
    
            if(config('configurazione.typeOfOrdering')){
                $res_c1 = $res['cucina_1'];
                $res_c2 = $res['cucina_2'];
                $av_c1 = $av['cucina_1'];
                $av_c2 = $av['cucina_2'];
                $np_c1 = $data['npezzi_c1'];
                $np_c2 = $data['npezzi_c2'];
                if(isset($data['comune'])){
                    if( ($res['domicilio'] + 1) < $av['domicilio']){
                        $res['domicilio'] = $res['domicilio'] + 1;
    
                        if((($res_c1 + $np_c1) < $av_c1) && (($res_c2 + $np_c2) < $av_c2)){
                            $res['cucina_1'] = $res['cucina_1'] + $np_c1;
                            $res['cucina_2'] = $res['cucina_2'] + $np_c2;
                        }elseif(($res_c1 + $np_c1) == $av_c1){
                            $res['cucina_1'] = $res['cucina_1'] + $np_c1;
                            $vis['cucina_1'] = 0;
                        }elseif(($res_c2 + $np_c2) == $av_c2){
                            $res['cucina_2'] = $res['cucina_2'] + $np_c2;
                            $vis['cucina_2'] = 0;
                        }elseif((($res_c1 + $np_c1) == $av_c1) && (($res_c2 + $np_c2) == $av_c2)){
                            $res['cucina_1'] = $res['cucina_1'] + $np_c1;
                            $vis['cucina_1'] = 0;
                            $res['cucina_2'] = $res['cucina_2'] + $np_c2;
                            $vis['cucina_2'] = 0;
                        }else{
                            return response()->json([
                                'success' => false,
                                'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata,  ci dispiace per l\'inconveniente... provate di nuovo',
                                'data' => $date
                            ]);
                        }
                    } elseif (($res['domicilio'] + 1) == $av['domicilio']){
                        $res['domicilio'] = $res['domicilio'] + 1;
                        $vis['domicilio'] = 0;
                        if((($res_c1 + $np_c1) < $av_c1) && (($res_c2 + $np_c2) < $av_c2)){
                            $res['cucina_1'] = $res['cucina_1'] + $np_c1;
                            $res['cucina_2'] = $res['cucina_2'] + $np_c2;
                        }elseif(($res_c1 + $np_c1) == $av_c1){
                            $res['cucina_1'] = $res['cucina_1'] + $np_c1;
                            $vis['cucina_1'] = 0;
                        }elseif(($res_c2 + $np_c2) == $av_c2){
                            $res['cucina_2'] = $res['cucina_2'] + $np_c2;
                            $vis['cucina_2'] = 0;
                        }elseif((($res_c1 + $np_c1) == $av_c1) && (($res_c2 + $np_c2) == $av_c2)){
                            $res['cucina_1'] = $res['cucina_1'] + $np_c1;
                            $vis['cucina_1'] = 0;
                            $res['cucina_2'] = $res['cucina_2'] + $np_c2;
                            $vis['cucina_2'] = 0;
                        }else{
                            return response()->json([
                                'success' => false,
                                'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata,  ci dispiace per l\'inconveniente... provate di nuovo',
                                'data' => $date
                            ]);
                        }
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata,  ci dispiace per l\'inconveniente... provate di nuovo',
                            'data' => $date
                        ]);
    
                    }
                }else{
                    if((($res_c1 + $np_c1) < $av_c1) && (($res_c2 + $np_c2) < $av_c2)){
                        $res['cucina_1'] = $res['cucina_1'] + $np_c1;
                        $res['cucina_2'] = $res['cucina_2'] + $np_c2;
                    }elseif(($res_c1 + $np_c1) == $av_c1){
                        $res['cucina_1'] = $res['cucina_1'] + $np_c1;
                        $vis['cucina_1'] = 0;
                    }elseif(($res_c2 + $np_c2) == $av_c2){
                        $res['cucina_2'] = $res['cucina_2'] + $np_c2;
                        $vis['cucina_2'] = 0;
                    }elseif((($res_c1 + $np_c1) == $av_c1) && (($res_c2 + $np_c2) == $av_c2)){
                        $res['cucina_1'] = $res['cucina_1'] + $np_c1;
                        $vis['cucina_1'] = 0;
                        $res['cucina_2'] = $res['cucina_2'] + $np_c2;
                        $vis['cucina_2'] = 0;
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata, ci dispiace per l\'inconveniente... provate di nuovo',
                            'data' => $date
                        ]);
                    }
    
                }
                $date->visible = json_encode($vis);
                $date->reserving = json_encode($res);
            }else{
                if(isset($data['comune'])){
                    if(($res['domicilio'] + 1) < $av['domicilio']){
                        $res['domicilio'] = $res['domicilio'] + 1;
                        $date->reserving = json_encode($res);  
                    }elseif(($res['domicilio'] + 1) == $av['domicilio']){
                        $res['domicilio'] = $res['domicilio'] + 1;
                        $date->reserving = json_encode($res);
                        $vis['domicilio'] = 0;
                        $date->visible = json_encode($vis);
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata,  ci dispiace per l\'inconveniente... provate di nuovo',
                            'data' => $date
                        ]);
                    }
                }else{
                    if(($res['asporto'] + 1) < $av['asporto']){
                        $res['asporto'] = $res['asporto'] + 1;
                        $date->reserving = json_encode($res);  
                    }elseif(($res['asporto'] + 1) == $av['asporto']){
                        $res['asporto'] = $res['asporto'] + 1;
                        $date->reserving = json_encode($res);
                        $vis['asporto'] = 0;
                        $date->visible = json_encode($vis);
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata, ci dispiace per l\'inconveniente... provate di nuovo',
                            'data' => $date
                        ]);
                    }
                }
            }
            $total_price = 0;
            for ($i = 0; $i < count($cart); ++$i) {
                $product = Product::where('id', $cart[$i]['id'])->first();
                $total_price += $product->price * $cart[$i]['counter'];
                $cart[$i]['price'] = $product->price;
       
                for ($z = 0; $z < count($cart[$i]['add']); $z++) {
                    $ingredient = Ingredient::where('name', $cart[$i]['add'][$z])->first();
                    $total_price += $ingredient->price * $cart[$i]['counter'];

                    $cart[$i]['price'] +=  $ingredient->price;
                }
                for ($z = 0; $z < count($cart[$i]['option']); $z++) {
                    $ingredient = Ingredient::where('name', $cart[$i]['option'][$z])->first();
                    $total_price += $ingredient->price * $cart[$i]['counter'];
                    
                    $cart[$i]['price'] +=  $ingredient->price;
                }
            }
            
    
            $newOrder = new Order();
            
            $newOrder->name = $data['name'];
            $newOrder->surname = $data['surname'];
            $newOrder->date_slot = $data['date_slot'];
            $newOrder->phone = $data['phone'];
            $newOrder->email = $data['email'];
            $newOrder->message = $data['message'];
            $newOrder->news_letter = $data['news_letter'];
            $newOrder->status = $data['paying'] ? 4 : 2;
            if (isset($data['comune'])) {
                $newOrder->comune = $data['comune'];
                $newOrder->address = $data['via'];
                $newOrder->address_n = $data['cv'];
                $delivery = true;

                $setting = Setting::where('name', 'Possibilità di consegna a domicilio')->first();
                $shipping_cost = json_decode($setting->property, 1);
                $newOrder->tot_price = $total_price + $shipping_cost['delivery_cost'] ;
            }else{
                $newOrder->tot_price = $total_price;
            }
            $newOrder->save();
            
            foreach ($cart as $e) {
                $item_order = new OrderProduct();
                $item_order->order_id = $newOrder->id;
                $item_order->product_id = $e['id'];
                $item_order->quantity= $e['counter'];
                $item_order->remove = json_encode($e['remove']);
                $item_order->add = json_encode($e['add']);
                $item_order->option = json_encode($e['option']);
                $item_order->save();
            }
            

            
            
            if($data['paying']){   
                
                $payment_url = $payment_controller->checkout($newOrder->products, $newOrder->id, $delivery);
                
                return response()->json([
                    'success'   => true,
                    'payment'   => true,
                    'url'       => $payment_url,
                    'orderId'   => $newOrder->id,
                ]);
                
            }else{
                
                $date->update();
                
                // Ottieni le impostazioni di contatto
                $set = Setting::where('name', 'Contatti')->firstOrFail();
                $p_set = json_decode($set->property, true);
                if(isset($p_set['telefono'])){
                    $telefono = $p_set['telefono'];
                }else{
                    $telefono = '3332222333';
                }
                
                $bodymail_a = [
                    'type' => 'or',
                    'to' => 'admin',
        
                    'order_id' => $newOrder->id,
                    'name' => $newOrder->name,
                    'surname' => $newOrder->surname,
                    'email' => $newOrder->email,
                    'date_slot' => $newOrder->date_slot,
                    'message' => $newOrder->message,
                    'phone' => $newOrder->phone,
                    'admin_phone' => $p_set['telefono'],
                    
                    'comune' => $newOrder->comune,
                    'address' => $newOrder->address,
                    'address_n' => $newOrder->address_n,
                    
                    'status' => $newOrder->status,
                    'cart' => $newOrder->products,
                    'total_price' => $newOrder->tot_price,
        
                    
                ];
                $bodymail_u = [
                    'type' => 'or',
                    'to' => 'user',
        
                    'order_id' => $newOrder->id,
                    'name' => $newOrder->name,
                    'surname' => $newOrder->surname,
                    'email' => $newOrder->email,
                    'date_slot' => $newOrder->date_slot,
                    'message' => $newOrder->message,
                    'phone' => $newOrder->phone,
                    'admin_phone' => $p_set['telefono'],
                    
                    'comune' => $newOrder->comune,
                    'address' => $newOrder->address,
                    'address_n' => $newOrder->address_n,
                    
                    'status' => $newOrder->status,
                    'cart' => $newOrder->products,
                    'total_price' => $newOrder->tot_price,
        
                    
                ];
                $mail = new confermaOrdineAdmin($bodymail_u);
                Mail::to($data['email'])->send($mail);
        
                $mailAdmin = new confermaOrdineAdmin($bodymail_a);
                Mail::to(config('configurazione.mail'))->send($mailAdmin);

                //$this->sendNotification();
                // $ordineId = $newOrder->id/* ID dell'ordine creato */;
                // $nomeCliente = $newOrder->name/* Nome del cliente */;
                // event(new NewOrderNotification($nomeCliente, $ordineId));

                return response()->json([
                    'success'   => true,
                    'payment'   => false,
                    'order'     => $newOrder,
                ]);
            }






        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errore del database: ' . $e->getMessage(),
            ]);
        } catch (Exception $e) {
            $trace = $e->getTrace();
            $errorInfo = [
                'success' => false,
                'error' => 'Si è verificato un errore durante l\'elaborazione della richiesta: ' . $e->getMessage(),

            ];

            return response()->json($errorInfo, 500);
        }

        

    }

    public function sendNotification()
    {
        // Imposta le intestazioni per SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        
        // Mantieni attivo il ciclo per continuare a inviare dati
        while (true) {
            if (connection_aborted()) {
                break; // Esce dal ciclo se la connessione viene interrotta
            }
            
            // Ottieni ordini non notificati
            $order = Order::where('notificated', 0)->where('status', '!=', 4)->get();
    
            if (count($order)) {
                $eventData = [];
                foreach ($order as $o) {
                    $eventData[] = [
                        'name'  => $o->name,
                        'id'    => $o->id,
                    ];
                    // Imposta notificato a 1 per evitare notifiche duplicate
                    $o->notificated = 1;
                    $o->update();
                }
    
                // Invia i dati formattati secondo lo standard SSE
                echo 'data: ' . json_encode($eventData) . "\n\n";
                
                // Forza l'invio immediato dei dati al client
                ob_flush();
                flush();
            }
    
            // Intervallo di attesa per ridurre il carico sul server
            sleep(7); // 5 secondi di pausa tra le verifiche
        }
    }    
    
}
