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
use App\Http\Controllers\Controller;
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
                //$cart[$i]['name'] = $product->name;
                
                for ($z = 0; $z < count($cart[$i]['add']); $z++) {
                    $ingredient = Ingredient::where('name', $cart[$i]['add'][$z])->first();
                    $total_price += $ingredient->price * $cart[$i]['counter'];

                    $cart[$i]['price'] +=  $ingredient->price;
                }
               // $cart[$i]['tot_price'];
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
            $newOrder->tot_price = $total_price;
            $newOrder->message = $data['message'];
            $newOrder->news_letter = $data['news_letter'];
            $newOrder->status = 4;
            if (isset($data['comune'])) {
                $newOrder->comune = $data['comune'];
                $newOrder->address = $data['via'];
                $newOrder->address_n = $data['cv'];
            }
            $newOrder->save();

            $payment_url = $payment_controller->checkout($cart, $newOrder->id);
            
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
            $date->update();

            // Ottieni le impostazioni di contatto
            $set = Setting::where('name', 'Contatti')->firstOrFail();
            $p_set = json_decode($set->property, true);
            if(isset($p_set['telefono'])){
                $telefono = $p_set['telefono'];
            }else{
                $telefono = '3332222333';
            }

            return response()->json([
                'payment'   => true,
                'url'       => $payment_url,
                'orderId'   => $newOrder->id,
            ]);

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
    public function store_nopay(Request $request)
    { 

        $request->validate($this->validations);
        
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
                //$cart[$i]['name'] = $product->name;
                
                for ($z = 0; $z < count($cart[$i]['add']); $z++) {
                    $ingredient = Ingredient::where('name', $cart[$i]['add'][$z])->first();
                    $total_price += $ingredient->price * $cart[$i]['counter'];

                    $cart[$i]['price'] +=  $ingredient->price;
                }
               // $cart[$i]['tot_price'];
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
            $newOrder->tot_price = $total_price;
            $newOrder->message = $data['message'];
            $newOrder->news_letter = $data['news_letter'];
            $newOrder->status = 2;
            if (isset($data['comune'])) {
                $newOrder->comune = $data['comune'];
                $newOrder->address = $data['via'];
                $newOrder->address_n = $data['cv'];
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
                'admin_phone' => $telefono,
                
                'comune' => $newOrder->comune,
                'address' => $newOrder->address,
                'address_n' => $newOrder->address_n,
                
                'status' => $newOrder->status,
                'cart' => $cart,
                'total_price' => $total_price,
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
                'admin_phone' => $telefono,
                
                'comune' => $newOrder->comune,
                'address' => $newOrder->address,
                'address_n' => $newOrder->address_n,
                
                'status' => $newOrder->status,
                'cart' => $cart,
                'total_price' => $total_price,
            ];
        
    
            $mail = new confermaOrdineAdmin($bodymail_u);
            Mail::to($data['email'])->send($mail);
    
            $mailAdmin = new confermaOrdineAdmin($bodymail_a);
            Mail::to(config('configurazione.mail'))->send($mailAdmin);

            return response()->json([
                'payment'   => false,
                'success' => true,
                'prenotazione' => $newOrder,
                'data' => $date
            ]);

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
    public function orderSuccess($orderData){
            
        $product = Product::where('id', $orderData)->first();

        $product->status = 3;

        $product->update();

        return redirect('http://localhost:5174/');
    }
}
