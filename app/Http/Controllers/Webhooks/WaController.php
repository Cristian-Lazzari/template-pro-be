<?php

namespace App\Http\Controllers\Webhooks;

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

class WaController extends Controller
{

    // Metodo per gestire i webhook
    public function handle(Request $request)
    {

        
        $data = $request->all();
        $number = $data['number'];
        //Log::info("dati ricevuti daam1: " . $data);

        $setting = Setting::where('name', 'wa')->first();
        $property = json_decode($setting->property, true);
        $numbers = $property['numbers'];
        $co_work = false;
        if(count($numbers) == 2){
            $co_work = true;
            $p = array_search($number, $numbers);
            $this->updateLastResponseWa($p);
            $p = $p == 0 ? 1 : 0;
            $number_correct = $numbers[$p];
        }else{
            $this->updateLastResponseWa(0);
        }
        $numebr = $data['wa_id'];
        $messageId = $data['wa_id'];
        $button_r = $data['response'];

        $order_ex = Order::where('whatsapp_message_id', 'like', '%' . $messageId . '%')->exists();
        if ($order_ex) {
            $order = Order::where('whatsapp_message_id', 'like', '%' . $messageId . '%')->first();
            if ($co_work && in_array($order->status, [1,3,5,6])) {
                $this->message_co_worker(1, $button_r, $p, $order, $number_correct);
            }
            $this->statusOrder($button_r, $order);
        } elseif (Reservation::where('whatsapp_message_id', 'like', '%' . $messageId . '%')->exists()) {
            
            $reservation = Reservation::where('whatsapp_message_id', 'like', '%' . $messageId . '%')->first();   // Se non trovato in Orders, cerca nella tabella rervations
            if ($reservation) {
                if ($co_work && in_array($reservation->status, [1,3,5,6])) {
                    $this->message_co_worker(false, $button_r, $p, $reservation, $number_correct);
                }
                $this->statusRes($button_r, $reservation);
            }
        } else {
            // Nessun ordine o prenotazione trovato per il Message ID
            Log::info("Nessun ordine o prenotazione trovati per il Message ID: " . $messageId);
        }
      
        return response()->json(['status' => 'success']);
    }

    protected function message_co_worker($o_r, $c_a, $p, $or_res, $number){
        try {
            Log::info("Inizio esecuzione message_co_worker", [
                'o_r' => $o_r,
                'c_a' => $c_a,
                'p' => $p,
                'or_res_id' => $or_res->id ?? 'N/A',
                'number' => $number
            ]);
    
            // Definizione dei messaggi in base allo stato
            $m = $o_r ? 'L\'ordine è stato ' : 'La prenotazione è stata ';
            $sub = $o_r ? 'L\'ordine' : 'La prenotazione';
    
            if ($c_a) {
                $m .= 'confermat' . ($o_r ? 'o ✅' : 'a ✅');
                $word = 'confermat' . ($o_r ? 'o ✅' : 'a ✅');
            } else {
                $m .= 'annullat' . ($o_r ? 'o ❌' : 'a ❌');
                $word = 'annullat' . ($o_r ? 'o ❌' : 'a ❌');
            }
    
            $m .= ' dal tuo collega';
    
            // Controllo se la risposta è entro 24 ore
            if ($this->isLastResponseWaWithin24Hours($p)) {
                // Verifica se il campo whatsapp_message_id esiste e contiene dati validi
                if (!isset($or_res->whatsapp_message_id)) {
                    throw new Exception("whatsapp_message_id mancante nell'ordine/prenotazione.");
                }
    
                $messages = json_decode($or_res->whatsapp_message_id, true);
                if (!is_array($messages) || !isset($messages[$p])) {
                    throw new Exception("Formato di whatsapp_message_id non valido.");
                }
    
                $old_id = $messages[$p];
    
                $data = [
                    'messaging_product' => 'whatsapp',
                    'to' => $number,
                    "type" => "text",
                    "context" => [
                        "message_id" => $old_id
                    ],
                    "text" => [
                        "body" => $m
                    ]
                ];
            } else {
                $data = [
                    'messaging_product' => 'whatsapp',
                    'to' => $number,
                    'category' => 'utility',
                    'type' => 'template',
                    "context" => [
                        "message_id" => $old_id ?? null
                    ],
                    'template' => [
                        'name' => 'responnse',
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
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
    
            $url = 'https://graph.facebook.com/v20.0/' . config('configurazione.WA_ID') . '/messages';
            
            $response = Http::withHeaders([
                'Authorization' => config('configurazione.WA_TO'),
                'Content-Type' => 'application/json'
            ])->post($url, $data);
    
            // Log della risposta ricevuta
            Log::info("Risposta WhatsApp inviata con successo", ['response' => $response->json()]);
    
            return $response->json();
        } catch (Exception $e) {
            Log::error("Errore in message_co_worker", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    protected function statusOrder($c_a, $order){
        Log::info("success");
        if($c_a == 1 && in_array($order->status, [1, 5])){
            return;
        }elseif($c_a == 0 && in_array($order->status, [0, 6])){
            return;
        }elseif(in_array($order->status, [1, 5, 0, 6])){
            return;
        }
        if($c_a == 1){
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

            
            
        }
        $order->update();
        

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
            
            'status' => $order->status,
            'cart' => $order->products,
            'total_price' => $order->tot_price,
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($order['email'])->send($mail);

    
        return $m;
    }
    protected function statusRes($c_a, $res){
        Log::info("success");
        if($c_a == 1 && in_array($res->status, [1, 5])){
            return;
        }elseif($c_a == 0 && in_array($res->status, [0, 6])){
            return;
        }elseif(in_array($res->status, [1, 5, 0, 6])){
            return;
        }
        if($c_a == 1){
            $res->status = 1;
            $m = 'La prenotazione e\' stata confermata correttamente';
            $message = 'Siamo felici di informarti che la tua prenotazione e\' stata confermata, ti ricordo la data e l\'orario che hai scelto: ' . $res->date_slot ;
        }else{
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
        }
        $res->update();
        

        $set = Setting::where('name', 'Contatti')->firstOrFail();
        $p_set = json_decode($set->property, true);
        $bodymail = [
            'type' => 'res',
            'to' => 'user',

            'res_id' => $res->id,
            'name' => $res->name,
            'surname' => $res->surname,
            'email' => $res->email,
            'date_slot' => $res->date_slot,
            'message' => $res->message,
            'sala' => $res->sala,
            'phone' => $res->phone,
            'admin_phone' => $p_set['telefono'],
               
            'n_person' => $res->n_person,
            'status' => $res->status,
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);

        Mail::to($res['email'])->send($mail);

        return;   
    }
    protected function updateLastResponseWa($p){
        // Trova il record con name = 'wa'
        $setting = Setting::where('name', 'wa')->first();

        // Decodifica il campo 'property' da JSON ad array
        $property = json_decode($setting->property, true);
        if($p == 0){
            $property['last_response_wa_1'] = Carbon::now()->toDateTimeString();
        }else if($p == 1){
            $property['last_response_wa_2'] = Carbon::now()->toDateTimeString();
        }
        // Aggiorna 'last_response_wa' con la data attuale

        // Ricodifica 'property' in JSON e aggiorna il record
        $setting->property = json_encode($property);
        $setting->update();

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


}
