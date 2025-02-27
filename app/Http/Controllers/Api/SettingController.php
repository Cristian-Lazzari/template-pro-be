<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use Stripe\Refund;
use Stripe\Stripe;
use App\Models\Date;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Reservation;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    public function client_default(Request $request) {
        $messageId = $request->query('whatsapp_message_id');
        //return $messageId;

        if (!$messageId) {
            return response()->json(['error' => 'whatsapp_message_id mancante'], 400);
        }
        
        // Cerca l'ordine o la prenotazione
        $order = Order::where('whatsapp_message_id', $messageId)->first();
        $reservation = Reservation::where('whatsapp_message_id', $messageId)->first();
        
        if (!$order && !$reservation) {
            return response()->json(['error' => 'Nessun ordine o prenotazione trovata'], 404);
        }
        
        // Determina quale entità annullare

        if ($order) {
            $or_res = $order;
            $status = $or_res->status;  
            if(in_array($status, [0, 6])){
                return view('guests.delete_success');
            }
            $link_id = config('configurazione.APP_URL') . '/admin/orders/' . $or_res->id;
            $this->statusOrder(0, $or_res);
            $o_r = 'or';
            
        } else {
            $or_res = $reservation;
            $status = $or_res->status;  
            if(in_array($status, [0, 6])){
                return view('guests.delete_success');
            }
            $link_id = config('configurazione.APP_URL') . '/admin/reservations/' . $or_res->id;
            $this->statusRes(0, $or_res);
            $o_r = 'res'; 
        }
        // 📲 **Invia il messaggio di annullamento su WhatsApp*
        $wa = Setting::where('name', 'wa')->first();
        $property = json_decode($wa->property, 1);
        $p = 0; 
       

            foreach ($property['numbers'] as $number) {
                $this->message_default($o_r, $p, $or_res, $number, $link_id );
                $p ++; 
            }


        return view('guests.delete_success');
    }
    public function index() {
        $settings = Setting::all();
        foreach ($settings as $s) {
            $string = json_decode($s['property'], true);  
            $s['property'] = $string;
        }

        return response()->json([
            'success' => true,
            'results' => $settings,
            'double_t'=> config('configurazione.double_t'),
        ]);
    }
    protected function message_default($o_r, $p, $or_res, $number, $link_id){
        try {
            Log::info("(SC) Inizio esecuzione message_default", [
                'o_r' => $o_r,
                'p' => $p,
                'or_res_id' => $or_res->id ?? 'N/A',
                'number' => $number
            ]);
    
            // Definizione dei messaggi in base allo stato
            $m = $o_r == 'or' ? 'L\'ordine è stato ' : 'La prenotazione è stata ';
            $sub = $o_r == 'or' ? 'L\'ordine è stato' : 'La prenotazione è stata';
     
            $m .= '*annullat' . ($o_r == 'or' ? 'o* ❌' : 'a* ❌');
            $word = 'annullat' . ($o_r == 'or' ? 'o ❌' : 'a ❌');

            $m .= ' dal *cliente*';
            
            // Controllo se la risposta è entro 24 ore
            $messages = json_decode($or_res->whatsapp_message_id, true);
            $old_id = $messages[$p];
            if ($this->isLastResponseWaWithin24Hours($p)) {
    
                $data = [
                    'messaging_product' => 'whatsapp',
                    'to' => $number,
                    "type" => "text",
                    "text" => [
                        "body" => $m
                    ],
                    "context" => [
                        "message_id" => $old_id
                    ],
                ];
            } else {
                $data = [
                    'messaging_product' => 'whatsapp',
                    'to' => $number,
                    'category' => 'utility',
                    'type' => 'template',
                    'template' => [
                        'name' => 'response_link',
                        'language' => [
                            'code' => 'it'
                        ],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    [
                                        'type' => 'text',
                                        'text' => $sub
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $word
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => 'cliente'
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $or_res->name . ' ' . $or_res->surname,
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $or_res->date_slot,
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $link_id,
                                    ],
                                ]
                            ]
                        ]
                    ],
                    "context" => [
                        "message_id" => $old_id
                    ],
                ];
            }
    
            $url = 'https://graph.facebook.com/v20.0/' . config('configurazione.WA_ID') . '/messages';
            
            $response = Http::withHeaders([
                'Authorization' => config('configurazione.WA_TO'),
                'Content-Type' => 'application/json'
            ])->post($url, $data);
    
            // Log della risposta ricevuta
            Log::info("(SC) Risposta WhatsApp inviata con successo", ['response' => $response->json()]);
    
            return $response->json();
        } catch (Exception $e) {
            Log::error("(SC) Errore in message_default", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    protected function isLastResponseWaWithin24Hours($p)
    {
        $setting = Setting::where('name', 'wa')->first();

        if ($setting) {
            // Decodifica il campo 'property' da JSON ad array
            $property = json_decode($setting->property, true);
            if($p == 0){
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
    protected function statusOrder($c_a, $order){
        if($c_a == 0 && in_array($order->status, [0, 6])){
            return;
        }
    
        if(in_array($order->status, [3, 5])){
            $m = 'L\'ordine è stata annullato e RIMBORSATO correttamente';
            
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
    
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
        }elseif(in_array($order->status, [2, 1])){
            $m = 'L\'ordine è stata annullato correttamente';
            $message = 'Ci dispiace informarti che purtroppo il tuo ordine è stato annullato';
            $order->status = 0;
        }else{
            $m = 'L\'ordine era già stato annullato!';
            return; 
        }
        $date = Date::where('date_slot', $order->date_slot)->firstOrFail();
        $vis = json_decode($date->visible, 1); 
        $reserving = json_decode($date->reserving, 1);
        if(config('configurazione.typeOfOrdering')){
            $np_cucina_1 = 0;
            $np_cucina_2 = 0;
            foreach ($order->products as $p) {
                $qt = 0;
                $op = OrderProduct::where('product_id', $p->id)->where('order_id', $order->id)->firstOrFail();
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
    
        $order->update();
        
        $set = Setting::where('name', 'Contatti')->firstOrFail();
        $p_set = json_decode($set->property, true);
        $bodymail = [
            'type' => 'or',
            'to' => 'user',
            
            'title' =>  'Come richiesto il tuo ordine è stato annullato',
            'subtitle' => $order->status == 6 ? 'Il tuo rimborso verrà elaborato in 5-10 gironi lavorativi' : '',
            'whatsapp_message_id' => $order->whatsapp_message_id,

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

            
            'status' => $order->status,
            'cart' => $order->products,
            'total_price' => $order->tot_price,
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($order['email'])->send($mail);

    
        return $m;
    }
    protected function statusRes($c_a, $res){
        if($c_a == 0 && in_array($res->status, [0, 6])){
            return;
        }
       
        if($res->status == 0){
            $m = 'La prenotazione e\' stata gia annullata correttamente';
            return;
        }
        $date = Date::where('date_slot', $res->date_slot)->firstOrFail();
        $vis = json_decode($date->visible, 1); 
        $reserving = json_decode($date->reserving, 1);
        $_p = json_decode($res->n_person);
        $tot_p = $_p->child + $_p->adult;
        if(config('configurazione.double_t')){
            if($res->sala == 1){
                if($vis['table_1'] == 0){
                    $vis['table_1'] = 1;
                }
                $reserving['table_1'] = $reserving['table_1'] - $tot_p;
                $date->reserving = json_encode($reserving);
                $date->visible = json_encode($vis);
                $date->update();
            }else{
                if($vis['table_2'] == 0){
                    $vis['table_2'] = 1;
                }
                $reserving['table_2'] = $reserving['table_2'] - $tot_p;
                $date->reserving = json_encode($reserving);
                $date->visible = json_encode($vis);
                $date->update();
            }
        }else{
            if($vis['table'] == 0){
                $vis['table'] = 1;
            }
            $reserving['table'] = $reserving['table'] - $tot_p;
            $date->reserving = json_encode($reserving);
            $date->visible = json_encode($vis);
            $date->update();
        }

        $res->status = 0;
        $m = 'La prenotazione e\' stata annullata correttamente';
        $message = 'Ci spiace informarti che la tua prenotazione e\' stata annullata per la data e l\'orario che hai scelto... ' . $res->date_slot ;
        
        $res->update();
        

        $set = Setting::where('name', 'Contatti')->firstOrFail();
        $p_set = json_decode($set->property, true);
        $bodymail = [
            'type' => 'res',
            'to' => 'user',

            'title' => 'Come richiesto la tua prenotazione è stata annullato',
            'subtitle' => '',

            'res_id' => $res->id,
            'name' => $res->name,
            'surname' => $res->surname,
            'email' => $res->email,
            'date_slot' => $res->date_slot,
            'message' => $res->message,
            'sala' => $res->sala,
            'phone' => $res->phone,
            'admin_phone' => $p_set['telefono'],
            
            'whatsapp_message_id' => $res->whatsapp_message_id,
            'n_person' => $res->n_person,
            'status' => $res->status,
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);

        Mail::to($res['email'])->send($mail);

        return;   
    }
}
