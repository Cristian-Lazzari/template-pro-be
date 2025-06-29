<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Date;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\MenuOrder;
use App\Models\Ingredient;
use App\Models\Reservation;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use Illuminate\Support\Facades\Log;
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
        $request->validate($this->validations);
        $data = $request->all();
        try {       
            $date = Date::where('date_slot', $data['date_slot'])->firstOrFail();
            $vis = json_decode($date->visible, true);
            $av = json_decode($date->availability, true);
            $res = json_decode($date->reserving, true);
            
            $arrvar = str_replace('\\', '', $data['cart']);
            $cart = json_decode($arrvar, true);
           // return response()->json($cart);
            $adv_s = Setting::where('name', 'advanced')->first();
            $property_adv = json_decode($adv_s->property, 1);  
           
            if($property_adv['too']){
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
                                'message' => 'Controlla meglio la disponibilità per l\'orario che hai scelto... Prova di nuovo!',
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
                                'message' => 'Controlla meglio la disponibilità per l\'orario che hai scelto... Prova di nuovo!',
                                'data' => $date
                            ]);
                        }
                    }else{
                        return response()->json([
                            'success' => false,
                            'message' => 'Controlla meglio la disponibilità per l\'orario che hai scelto... Prova di nuovo!',
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
                            'message' => 'Controlla meglio la disponibilità per l\'orario che hai scelto... Prova di nuovo!',
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
            for ($i = 0; $i < count($cart['products']); ++$i) {
                $product = Product::where('id', $cart['products'][$i]['id'])->first();
                $total_price += $product->price * ($cart['products'][$i]['counter'] > 0 ? $cart['products'][$i]['counter'] : 1);
                $cart['products'][$i]['price'] = $product->price;
       
                for ($z = 0; $z < count($cart['products'][$i]['add']); $z++) {
                    $ingredient = Ingredient::where('name', $cart['products'][$i]['add'][$z])->first();
                    $total_price += $ingredient->price * ($cart['products'][$i]['counter'] > 0 ? $cart['products'][$i]['counter'] : 1);
                }
                for ($z = 0; $z < count($cart['products'][$i]['option']); $z++) {
                    $ingredient = Ingredient::where('name', $cart['products'][$i]['option'][$z])->first();
                    $total_price += $ingredient->price * ($cart['products'][$i]['counter'] > 0 ? $cart['products'][$i]['counter'] : 1);
                }
            }
            foreach ($cart['menus'] as $m) {
                $menu = Menu::where('id', $m['id'])->first();
                $total_price += $menu->price * ($m['counter'] ? $m['counter'] : 1);
                if($menu->fixed_menu == '2'){
                    foreach ($menu->products as $p) {  
                        if(in_array($p->id, array_column($m['products'], 'id'))){
                            $total_price += $p->pivot->extra_price * ($m['counter'] > 0 ? $m['counter'] : 1);
                        } 
                    }
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
            $tot_delivery_cost = 0;
            $newOrder->status = $data['paying'] ? 4 : 2;
            if (isset($data['comune'])) {
                $newOrder->comune = $data['comune'];
                $newOrder->address = $data['via'];
                $newOrder->address_n = $data['cv'];

                
                $setting = Setting::where('name', 'Possibilità di consegna a domicilio')->first();
                $setting_1 = Setting::where('name', 'Comuni per il domicilio')->first();
                $shipping_cost = json_decode($setting->property, 1);
                $tot_delivery_cost += $shipping_cost['delivery_cost'];
                $comuni = json_decode($setting_1->property, 1);
                foreach ($comuni as $c) {
                    if($c['cap'] == $data['cap']){
                        $total_price +=$c['price'];
                        $tot_delivery_cost +=$c['price'];
                    }
                }

                $newOrder->tot_price = $total_price + $shipping_cost['delivery_cost'];
            }else{
                $newOrder->tot_price = $total_price;
            }
            $newOrder->save();
            /// controllo se ci sono menu e in caso li aggiungo
            foreach ($cart['products'] as $e) {
                $item_order = new OrderProduct();
                $item_order->order_id = $newOrder->id;
                $item_order->product_id = $e['id'];
                $item_order->quantity= $e['counter'] > 0 ? $e['counter'] : 1;
                $item_order->remove = json_encode($e['remove']);
                $item_order->add = json_encode($e['add']);
                $item_order->option = json_encode($e['option']);
                $item_order->save();
            }
            
            foreach ($cart['menus'] as $m) {
                $item_order = new MenuOrder();
                $item_order->order_id = $newOrder->id;
                $item_order->menu_id = $m['id'];
                $item_order->quantity= $m['counter'] > 0 ? $m['counter'] : 1;
                $item_order->choices = $m['combo_menu'] ? json_encode(array_column($m['products'], 'id')) : '1';
                $item_order->save();
            }

            
            

            
            if($data['paying']){   
                $payment_controller = new PaymentController();
                
                $payment_url = $payment_controller->checkout($newOrder->products, $newOrder->id, $tot_delivery_cost, $newOrder->menus);
                
                return response()->json([
                    'success'   => true,
                    'payment'   => true,
                    'url'       => $payment_url,
                    'orderId'   => $newOrder->id,
                ]);
                
            }else{
                $date->update();
                $info = $newOrder->name . ' ' . $newOrder->surname .' ha ordinato per il ' . $newOrder->date_slot . ": \n\n";
                // Itera sui prodotti dell'ordine
                $order_mess = "";
                $type_mess = "";

                foreach ($newOrder->menus as $menu) {
                    // Aggiungi il nome e la quantità del prodotto
                    $info .= "☞ ";
                    $order_mess .= "☞ ";
                    if ($menu->pivot->quantity !== 1) {
                        $info .= "** {$menu->pivot->quantity}* ";
                        $order_mess .= "** {$menu->pivot->quantity}* ";
                    }
                    $info .= "*```" . $menu->name. "```*";
                    $order_mess .= "*```" . $menu->name. "```*";
                    // Gestisci le opzioni del prodotto
                    $info .= "\n ```Prodotti:``` " ;
                    $order_mess .= " ```Prodotti:``` " ;
                    if ($menu->fixed_menu == '2') {
                        $count = 1;
                        foreach (json_decode($menu->pivot->choices) as $id) {
                            $p_name = Product::where('id', $id)->first()->name;
                            if($count == count(json_decode($menu->pivot->choices))){
                                $info .= "\n " . $p_name . ".  " ;
                                $order_mess .= $p_name . ".  " ;
                            }else{
                                $info .= "\n " . $p_name . ", " ;
                                $order_mess .= $p_name . ", " ;
                            }
                            $count ++;
                        }
                    }else{
                        $count = 1;
                        foreach ($menu->products as $p) {
                            if($count == count($menu->products)){
                                $info .= "\n " . $p->name . ".  " ;
                                $order_mess .= $p->name . ".  " ;
                            }else{
                                $info .= "\n " . $p->name . ", " ;
                                $order_mess .= $p->name . ", " ;
                            }
                            $count ++;
                        }
                    }
                    // Separatore tra i prodotti
                    $info .= " \n\n";
                    $order_mess .= " " . " ";
                }

                foreach ($newOrder->products as $product) {
                    // Aggiungi il nome e la quantità del prodotto
                    $info .= "☞ ";
                    $order_mess .= "☞ ";
                    if ($product->pivot->quantity !== 1) {
                        $info .= "** {$product->pivot->quantity}* ";
                        $order_mess .= "** {$product->pivot->quantity}* ";
                    }
                    $info .= "*```" . $product->name. "```*";
                    $order_mess .= "*```" . $product->name. "```*";

                    // Gestisci le opzioni del prodotto
                    if ($product->pivot->option !== '[]') {
                        $options = json_decode($product->pivot->option);
                        $info .= "\n ```Opzioni:``` " . implode(', ', $options);
                        $order_mess .= " ```Opzioni:``` " . implode(', ', $options);
                    }
                    // Gestisci gli ingredienti aggiunti
                    if ($product->pivot->add !== '[]') {
                        $addedIngredients = json_decode($product->pivot->add);
                        $info .= "\n ```Aggiunte:``` " . implode(', ', $addedIngredients);
                        $order_mess .= " ```Aggiunte:``` " . implode(', ', $addedIngredients);
                    }
                    // Gestisci gli ingredienti rimossi
                    if ($product->pivot->remove !== '[]') {
                        $removedIngredients = json_decode($product->pivot->remove);
                        $info .= "\n ```Rimossi:``` " . implode(', ', $removedIngredients);
                        $order_mess .= " ```Rimossi:``` " . implode(', ', $removedIngredients);
                    }
                    // Separatore tra i prodotti
                    $info .= " \n\n";
                    $order_mess .= " " . " ";
                }
                if($newOrder->comune){
                    $info .= "Consegna a domicilio: {$newOrder->address}, {$newOrder->address_n}, {$newOrder->comune} ";
                    $type_mess .= "Consegna a domicilio: {$newOrder->address}, {$newOrder->address_n}, {$newOrder->comune} ";
                }else{
                    $info .= "Ritiro asporto";
                    $type_mess .= "Ritiro asporto";
                }
                $link_id = config('configurazione.APP_URL') . '/admin/orders/' . $newOrder->id;
                $t = $newOrder->comune ? "Ordine a domicilio" : "Ordine d'asporto";
                if($newOrder->message){
                    $info .= "Note: " . $newOrder->message . " \n";
                }
                $info = 'Contenuto della notifica: *_' . $t . "_* \n\n" . $info . "\n\n" .
                    "📞 Chiama: " . $newOrder->phone . "\n\n" .
                    "🔗 Vedi dalla Dashboard: $link_id";
                // Definisci l'URL della richiesta
                $url = 'https://graph.facebook.com/v20.0/'. config('configurazione.WA_ID') . '/messages';

                $numbers_wa_set_s = Setting::where('name', 'wa')->firstOrFail();
                $numbers_wa_set = json_decode($numbers_wa_set_s->property, true);

                $data_i = [
                    'messaging_product' => 'whatsapp',
                    'to' => '',
                    "type"=> "interactive",
                    "interactive"=> [
                        "type"=> "button",
                        "header"=> [
                            "type" => "text",
                            "text"=>'Hai una nuova notifica!',
                        ],  
                        "footer"=> [
                            "text"=> "Powered by F+"
                        ],
                        "body"=> [
                        "text"=> $info,
                        ],
                            "action"=> [
                            "buttons"=> [
                                [
                                    "type"=> "reply",
                                    "reply"=> [
                                        "id"=> "Conferma",
                                        "title"=> "Conferma"
                                    ]
                                ],
                                    [
                                    "type"=> "reply",
                                    "reply"=> [
                                        "id"=> "Annulla",
                                        "title"=> "Annulla"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];

                $data_t = [
                    'messaging_product' => 'whatsapp',
                    'to' => '',
                    'category' => 'utility',
                    'type' => 'template',
                    'template' => [
                        'name' => 'full_emoji',
                        'language' => [
                            'code' => 'it'
                        ],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    [
                                        'type' => 'text',
                                        'text' => $newOrder->comune ? 'Ordine a domicilio' : 'Ordine d\'asporto', 
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $newOrder->name . ' ' . $newOrder->surname . ' ha ordinato per il ' . $newOrder->date_slot  . ': '
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $order_mess
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $type_mess
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $newOrder->phone,  
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $link_id,  
                                    ],
                                ]
                            ]
                        ]
                    ]
                ];
                
                $n = 0;
                $messageId = [];
                $type_m_1 = false;
                $type_m_2 = false;
                foreach ($numbers_wa_set['numbers'] as $num) {
                    $data_t['to'] = $num;
                    $data_i['to'] = $num;
                    if($this->isLastResponseWaWithin24Hours($n)){
                        if($n == 1){
                            $type_m_1 = 0;
                        }else{     
                            $type_m_2 = 0;
                        }
                        $response = Http::withHeaders([
                            'Authorization' => config('configurazione.WA_TO'),
                            'Content-Type' => 'application/json'
                        ])->post($url, $data_i);
                        $m_id = $response->json()['messages'][0]['id'] ?? null;
                        if($m_id){
                            array_push($messageId, $m_id);
                        }
                    }else{
                        if($n == 1){
                            $type_m_1 = 1;
                        }else{     
                            $type_m_2 = 1;
                        }
                        $response = Http::withHeaders([
                            'Authorization' => config('configurazione.WA_TO'),
                            'Content-Type' => 'application/json'
                        ])->post($url, $data_t);
                        $m_id = $response->json()['messages'][0]['id'] ?? null;
                        if($m_id){
                            array_push($messageId, $m_id);
                        }
                    }
                    $n ++;
                }
                
                $newOrder->whatsapp_message_id = json_encode($messageId);
                $newOrder->update();

                $data_am1 = [        
                    'wa_id' => $newOrder->whatsapp_message_id,
                    'type_1' => $type_m_1,
                    'type_2' => $type_m_2,
                    'source' => config('configurazione.APP_URL'),
                ];
                $set = Setting::where('name', 'Contatti')->firstOrFail();
                $p_set = json_decode($set->property, true);
                if(isset($p_set['telefono'])){
                    $telefono = $p_set['telefono'];
                }else{
                    $telefono = '3332222333';
                }
                //new menu
                $product_r = [];
                foreach ($newOrder->products as $p) {
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
                    'menus' => $newOrder->menus,
                ];
                
                $cart_price = 0;
                $delivery_cost = 0;
                if($newOrder->comune){
                    foreach ($newOrder->products as $o) {
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
                    foreach ($newOrder->menus as $menu) {
                        $cart_price += $menu->price * ($menu->pivot->quantity ? $menu->pivot->quantity : 1);
                        if($menu->fixed_menu == '2'){
                            foreach ($menu->products as $p) {  
                                if(in_array($p->id, $menu->products->pluck('id')->all())){
                                    $cart_price += $p->pivot->extra_price * ($menu->pivot->quantity > 0 ? $menu->pivot->quantity : 1);
                                } 
                            }
                        }
                    }
                    $delivery_cost = $newOrder->tot_price - $cart_price;
                }
                //new menu
                $adv_s = Setting::where('name', 'advanced')->first();
                $property_adv = json_decode($adv_s->property, 1);
                $bodymail = [
                    'type' => 'or',
                    'to' => 'admin',

                    'title' =>  $newOrder->name . ' ha appena fatto un ordine ' . ($newOrder->comune ? 'a domicilio' : 'd\'asporto'),
                    'subtitle' => '',
                    
        
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
                    'delivery_cost' => $delivery_cost,
                    
                    'whatsapp_message_id' => null,
                    'status' => $newOrder->status,
                    'cart' => $cart_mail,
                    'total_price' => $newOrder->tot_price,
                    
                    'property_adv' => $property_adv,
                    
                ];
                $mailAdmin = new confermaOrdineAdmin($bodymail);
                Mail::to(config('configurazione.mail'))->send($mailAdmin);
                
                $bodymail['to'] = 'user';
                $bodymail['whatsapp_message_id'] = $newOrder->whatsapp_message_id;
                $bodymail['title'] = 'Ciao ' . $newOrder->name . ', grazie per aver ordinato tramite il nostro sito web';
                $bodymail['subtitle'] = 'Il tuo ordine è nella nostra coda, a breve riceverai l\'esito del processamento';


                $mail = new confermaOrdineAdmin($bodymail);
                Mail::to($data['email'])->send($mail);

                
                // Log dei dati inviati
                Log::info('Invio richiesta POST a https://db-demo4.future-plus.it/api/messages', $data_am1);
                
                try {
                    // Log dei dati inviati
                    Log::info('Dati inviati alla API:', $data_am1);
                    
                    // Invio della richiesta POST
                    $response_am1 = Http::post('https://db-demo4.future-plus.it/api/messages', $data_am1);
                
                    // Controllo della risposta prima di restituirla
                    if ($response_am1->successful()) {
                        Log::info('Risposta ricevuta con successo:');
                        Log::info($response_am1);
                     //   Log::info('Risposta ricevuta con successo:', $response_am1);
                        return response()->json([
                            'status' => 'success',
                            'success' => true,
                            'data' => $response_am1->json(),
                        ]);
                    } else {
                        Log::error('Errore nella risposta API:', [
                            'status' => $response_am1->status(),
                            'body' => $response_am1->body(),
                        ]);
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Errore dalla API esterna.',
                        ], $response_am1->status());
                    }
                } catch (Exception $e) {
                    // Gestione degli errori
                    Log::error('Errore nell\'invio della richiesta POST:', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Errore durante l\'invio della richiesta.',
                    ], 500);
                }    
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
    protected function isLastResponseWaWithin24Hours($n)
    {
        $setting = Setting::where('name', 'wa')->first();

        if ($setting) {
            // Decodifica il campo 'property' da JSON ad array
            $property = json_decode($setting->property, true);
            if($n == 0){
                 // Controlla se 'last_response_wa' è impostato
                if (isset($property['last_response_wa_1']) && !empty($property['last_response_wa_1'])) {
                    // Confronta la data salvata con le ultime 24 ore
                    $lastResponseDate = Carbon::parse($property['last_response_wa_1']);
                    return $lastResponseDate->greaterThanOrEqualTo(Carbon::now()->subHours(24));
                }
            }else{
                 // Controlla se 'last_response_wa' è impostato
                if (isset($property['last_response_wa_2']) && !empty($property['last_response_wa_2'])) {
                    // Confronta la data salvata con le ultime 24 ore
                    $lastResponseDate = Carbon::parse($property['last_response_wa_2']);
                    return $lastResponseDate->greaterThanOrEqualTo(Carbon::now()->subHours(24));
                }
            }
        }else{
            return false; // Se il record non esiste o la data non è impostata
        }
    }

}
