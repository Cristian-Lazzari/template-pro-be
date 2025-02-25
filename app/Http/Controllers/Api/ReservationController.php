<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Date;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    private $validations = [
        'name'      => 'required|string|max:50',
        'phone'     => 'required|string|max:20',
        'email'     => 'required|email|max:100',
        'n_adult'  => 'required|string|max:10',
        'n_child'  => 'required|string|max:10',
        'message'   => 'nullable|string|max:1000',
    ];

    public function store(Request $request)
    {
        try {
            // Validazione della richiesta
            $request->validate($this->validations);

            // Ottieni i dati dalla richiesta
            $data = $request->all();
            
            // Cerca la data corrispondente
            $date = Date::where('date_slot', $data['date_slot'])->firstOrFail();
            $vis = json_decode($date->visible, true);
            $av = json_decode($date->availability, true);
            $res = json_decode($date->reserving, true);

            // Calcola numero di persone
            $n_adult = intval($data['n_adult']);
            $n_child = intval($data['n_child']);
            $tot_p = $n_adult + $n_child;
            $n_person = [
                'adult' => $n_adult,
                'child' => $n_child,
            ];
            // Controlla la disponibilità e aggiorna le prenotazioni
            if(config('configurazione.double_t')){
                $sala = $data['sala'];
                if($sala == 1){
                    if(($res['table_1'] + $tot_p) < $av['table_1']){
                        $res['table_1'] = $res['table_1'] + $tot_p;
                        $date->reserving = json_encode($res);
                    } elseif(($res['table_1'] + $tot_p) == $av['table_1']) {
                        $res['table_1'] = $res['table_1'] + $tot_p;
                        $date->reserving = json_encode($res);
                        $vis['table_1'] = 0;
                        $date->visible = json_encode($vis);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata, ci dispiace per l\'inconveniente... provate di nuovo',
                            'data' => $date
                        ]);
                    }
                }else{
                    if(($res['table_2'] + $tot_p) < $av['table_2']){
                        $res['table_2'] = $res['table_2'] + $tot_p;
                        $date->reserving = json_encode($res);
                    } elseif(($res['table_2'] + $tot_p) == $av['table_2']) {
                        $res['table_2'] = $res['table_2'] + $tot_p;
                        $date->reserving = json_encode($res);
                        $vis['table_2'] = 0;
                        $date->visible = json_encode($vis);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata, ci dispiace per l\'inconveniente... provate di nuovo',
                            'data' => $date
                        ]);
                    }
                }
            }else{
                if(($res['table'] + $tot_p) < $av['table']){
                    $res['table'] = $res['table'] + $tot_p;
                    $date->reserving = json_encode($res);
                } elseif(($res['table'] + $tot_p) == $av['table']) {
                    $res['table'] = $res['table'] + $tot_p;
                    $date->reserving = json_encode($res);
                    $vis['table'] = 0;
                    $date->visible = json_encode($vis);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata, ci dispiace per l\'inconveniente... provate di nuovo',
                        'data' => $date
                    ]);
                }
            }

        
            // Crea la nuova prenotazione
            $newRes = new Reservation();
            $newRes->name = $data['name'];
            $newRes->surname = $data['surname'];
            $newRes->phone = $data['phone'];
            $newRes->email = $data['email'];
            $newRes->date_slot = $data['date_slot'];
            $newRes->n_person = json_encode($n_person);
            $newRes->message = $data['message'];
            $newRes->status = 2;
            $newRes->news_letter = $data['news_letter'];
            if(config('configurazione.double_t')){
                $newRes->sala = $data['sala'];
            }
            
            $date->update();
            $newRes->save();

 

            $info = $newRes->name . " " . $newRes->surname ." ha prenotato per il: " . $newRes->date_slot . " \n\n 🧑‍🧑‍🧒‍🧒 gli ospiti sono: ";
            $guest = "";
            $sala_mess = " ";
            if($n_adult && $n_child){
                $info .= $n_adult . " adulti e " . $n_child . " bambini \n\n";
                $guest .= $n_adult . " adulti e " . $n_child . " bambini ";
            }elseif($n_adult){
                $info .= $n_adult . " adulti \n\n";
                $guest .= $n_adult . " adulti ";
            }elseif($n_child){
                $info .= $n_child . " bambini \n\n";
                $guest .= $n_child . " bambini ";
            }
            if (config("configurazione.double_t") && $newRes->sala ) {
                $info .= " *_Sala prenota: ";
                $sala_mess .= " *_Sala prenota: ";
                if ($newRes->sala == 1) {
                    $info .= config("configurazione.set_time_dt")[0];
                    $sala_mess .= config("configurazione.set_time_dt")[0];
                }else{
                    $info .= config("configurazione.set_time_dt")[1];
                    $sala_mess .= config("configurazione.set_time_dt")[1];
                }
                $info .="_* \n\n ";
                $sala_mess .="_*";
            }
            $link_id = config('configurazione.APP_URL') . '/admin/reservations/' . $newRes->id;
            $info = "Contenuto della notifica: *_Prenotazione tavolo_* \n\n" . $info . "\n\n" .
            "📞 Chiama: " . $newRes->phone . "\n\n" .
            "🔗 Vedi dalla Dashboard: $link_id";
            
           

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
                        "text"=> "Powered by F +"
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
                                    'text' => 'Prenotazione tavolo', 
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $newRes->name . ' ' . $newRes->surname . ' ha prenotato un tavolo per il ' . $newRes->date_slot  
                                ],
                                [
                                    'type' => 'text',
                                    'text' => '🧑‍🧑‍🧒‍🧒 Gli ospiti sono: ' . $guest 
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $sala_mess,  
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $newRes->phone,  
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
            
            $newRes->whatsapp_message_id = json_encode($messageId);
            $newRes->update();
            
            $this->send_mail($newRes);

            $data_am1 = [        
                'wa_id' => $newRes->whatsapp_message_id,
                'type_1' => $type_m_1,
                'type_2' => $type_m_2,
                'source' => config('configurazione.APP_URL'),
            ];
            
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

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Errore nel trovare una risorsa
            return response()->json([
                'success' => false,
                'message' => 'Data o impostazione non trovata: ' . $e->getMessage(),
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Errore di validazione
            return response()->json([
                'success' => false,
                'message' => 'Errore di validazione: ' . $e->getMessage(),
                'errors' => $e->errors(),
            ], 200);

        } catch (\Exception $e) {
            // Gestione generale degli errori
            return response()->json([
                'success' => false,
                'message' => 'Si è verificato un errore: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 200);
        }
    }

    protected function send_mail($newRes){
        try{
            // Ottieni le impostazioni di contatto
            $set = Setting::where('name', 'Contatti')->firstOrFail();
            $p_set = json_decode($set->property, true);
            if(isset($p_set['telefono'])){
                $telefono = $p_set['telefono'];
            }else{
                $telefono = '3332222333';
            }
            // Prepara i dati per le email
            $bodymail = [
                'type' => 'res',
                'to' => 'admin',

                'title' =>  $newRes->name . ' ' . $newRes->surname .' ha appena prenotato un tavolo',
                'subtitle' => '',
                
                'res_id' => $newRes->id,
                'name' => $newRes->name,
                'surname' => $newRes->surname,
                'date_slot' => $newRes->date_slot,
                'message' => $newRes->message,
                'sala' => $newRes->message,
                'phone' => $newRes->phone,
                'admin_phone' => $telefono,
                'n_person' => $newRes->n_person,
                'status' => $newRes->status,
                'whatsapp_message_id' => $newRes->whatsapp_message_id,
            ];

            // Invia le email
            $mailAdmin = new confermaOrdineAdmin($bodymail);
            Mail::to(config('configurazione.mail'))->send($mailAdmin);

            $bodymail['to'] = 'user';
            $bodymail['whatsapp_message_id'] = $newRes->whatsapp_message_id;
            $bodymail['title'] = 'Ciao ' . $newRes->name . ', grazie per aver prenotato tramite il nostro sito web';
            $bodymail['subtitle'] = 'Il tuo ordine è nella nostra coda, a breve riceverai l\'esito del processamento';
            
            $mail = new confermaOrdineAdmin($bodymail);
            Mail::to($newRes['email'])->send($mail);
            return;
        } catch (\Exception $e) {
            // Gestione generale degli errori
            return response()->json([
                'success' => false,
                'message' => 'Si è verificato un errore: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 200);
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