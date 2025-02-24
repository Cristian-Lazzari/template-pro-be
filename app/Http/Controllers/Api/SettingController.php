<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Reservation;
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
            $order->status = 0;
            $order->update();
            $or_res = $order;
            $o_r = 'or';
            $bodymail = [
                'comune' => $or_res->comune,
                'address' => $or_res->address,
                'address_n' => $or_res->address_n,
                'cart' => $or_res->products,
                'total_price' => $or_res->tot_price,
            ];
        } else {
            $reservation->status = 0;
            $reservation->update();
            $or_res = $reservation;
            $o_r = 'res';
            $bodymail = [
                'n_person' => $or_res->n_person,         
            ];
        }

        

        $setting = Setting::where('name', 'wa')->first();
        $numbers = json_decode($setting->property, true);

        // 📲 **Invia il messaggio di annullamento su WhatsApp*
        $p = 1; 
        foreach ($numbers as $number) {
            $this->message_default($o_r, $p, $or_res, $number);
            $p ++; 
        }

        $set = Setting::where('name', 'Contatti')->firstOrFail();
        $p_set = json_decode($set->property, true);
        

        $bodymail['to'] = 'user';
        $bodymail['type'] = $o_r;
        $bodymail['order_id'] = $or_res->id;
        $bodymail['name'] = $or_res->name;
        $bodymail['surname'] = $or_res->surname;
        $bodymail['email'] = $or_res->email;
        $bodymail['date_slot'] = $or_res->date_slot;
        $bodymail['message'] = $or_res->message;
        $bodymail['phone'] = $or_res->phone;
        $bodymail['admin_phone'] = $p_set['telefono'];
        $bodymail['status'] = $or_res->status;
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($or_res['email'])->send($mail);

        return response()->json([
            'success' => true,
            'or_res' => $or_res,
            'message' => "Il ... è stato annullato con successo.",
        ]);
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
    protected function message_default($o_r, $p, $or_res, $number){
        try {
            Log::info("Inizio esecuzione message_default", [
                'o_r' => $o_r,
                'p' => $p,
                'or_res_id' => $or_res->id ?? 'N/A',
                'number' => $number
            ]);
    
            // Definizione dei messaggi in base allo stato
            $m = $o_r == 'or' ? 'L\'ordine è stato ' : 'La prenotazione è stata ';
            $sub = $o_r == 'or' ? 'L\'ordine è stato' : 'La prenotazione è stata';
     
            $m .= '*annullat' . ($o_r == 'or' ? 'o* ❌' : 'a* ❌');
            $word = '*annullat' . ($o_r == 'or' ? 'o* ❌' : 'a* ❌');

            $m .= ' dal *cliente*';
    
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
                        'name' => 'response',
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
            Log::error("Errore in message_default", [
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
}
