<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Date;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use Illuminate\Support\Facades\DB;
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
        $request->validate($this->validations);

        $data = $request->all();

        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);

        $carbonDate = Carbon::createFromFormat('Y-m-d H:i', $data['date_slot']);
        // Convertilo nel formato desiderato
        $f_date = $carbonDate->copy()->format('Y-m-d');
        $f_time = $carbonDate->copy()->format('H:i');
        $f_N = $carbonDate->copy()->format('N'); //giorno della settimana
        $av = 0;
        
        if($property_adv['week_set'][$f_N] !== [] && isset($property_adv['week_set'][$f_N][$f_time]) && in_array(1, $property_adv['week_set'][$f_N][$f_time]) && !isset($property_adv['day_off'][$f_date])){
            if(!$adv_s['dt']){
                $av = $property_adv['max_table'];
            }elseif($adv_s['dt'] && $data['sala'] == 1){
                $av = $property_adv['max_table_1'];
            }elseif($adv_s['dt'] && $data['sala'] == 2){
                $av = $property_adv['max_table_2'];
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Sembra che le disponibilità siano cambiate mentre procedevi con la prenotazione',
                'r' => '56'
            ]);
        }

        $res_in_time = Reservation::where('date_slot', $data['date_slot'])->get();

        if(count($res_in_time)){
            foreach ($res_in_time as $r) {
                $p_= json_decode($r->n_person, 1);
                $n_adult = $p['adult'];
                $n_child = $p['child'];
                $tot_p = $n_adult + $n_child;
                $av -= $tot_p;
                if($av < 0){
                    return response()->json([
                'success' => false,
                'message' => 'Sembra che le disponibilità siano cambiate mentre procedevi con la prenotazione',
                'r' => '73'
            ]);
                }
            }
        }
        $n_adult = intval($data['n_adult']);
        $n_child = intval($data['n_child']);
        $tot_p = $n_adult + $n_child;
        $av -= $tot_p;
        if($av < 0){
            return response()->json([
                'success' => false,
                'message' => 'Sembra che le disponibilità siano cambiate mentre procedevi con la prenotazione',
                'r' => '86'
            ]);
        }

    
        // Crea la nuova prenotazione
        $newRes = new Reservation();
        $newRes->name = $data['name'];
        $newRes->surname = $data['surname'];
        $newRes->phone = $data['phone'];
        $newRes->email = $data['email'];
        $newRes->date_slot = $carbonDate->copy()->format('d/m/Y H:i');
        $newRes->n_person = json_encode([
            'adult' => $data['n_adult'],
            'child' => $data['n_child'],
        ]);
        $newRes->message = $data['message'];
        $newRes->status = 2;
        $newRes->news_letter = $data['news_letter'];
        if($property_adv['dt']){
            $newRes->sala = $data['sala'];
        }
        

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
        if ($property_adv['dt'] && $newRes->sala ) {
            $info .= " *_Sala prenota: ";
            $sala_mess .= "Sala prenota: *_";
            if ($newRes->sala == 1) {
                $info .= $newRes->sala ?  $property_adv['sala_1'] : $property_adv['sala_2'];
                $sala_mess .= $newRes->sala ?  $property_adv['sala_1'] : $property_adv['sala_2'];
            }else{
                $info .= $newRes->sala ?  $property_adv['sala_1'] : $property_adv['sala_2'];
                $sala_mess .= $newRes->sala ?  $property_adv['sala_1'] : $property_adv['sala_2'];
            }
            $info .="_* \n\n ";
            $sala_mess .="_*";
        }
        if($newRes->message){
            $info .= "Note: " . $newRes->message . " \n";
        }
        $link_id = config('configurazione.APP_URL') . '/admin/reservations/' . $newRes->id;
        $info = "Contenuto della notifica: *_Prenotazione tavolo_* \n\n" . $info . "\n\n" .
        "📞 Chiama: " . $newRes->phone . "\n\n" .
        "🔗 Vedi dalla Dashboard: $link_id";
        
        

        $url = 'https://graph.facebook.com/v24.0/'. config('configurazione.WA_ID') . '/messages';

        $numbers_wa_set_s = Setting::where('name', 'wa')->firstOrFail();
        $numbers_wa_set = json_decode($numbers_wa_set_s->property, true);

        $data_i = [
            'messaging_product' => 'whatsapp',
            "recipient_type" => "individual",
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
            "recipient_type" => "individual",
            'to' => '',
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

                return response()->json([
                    'success' => false,
                    'message' => 'no',
                    'data' => $response->json(),
                ]);

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

                return response()->json([
                    'success' => false,
                    'message' => 'no',
                    'data' => $response->json(),
                ]);
            }
            $n ++;
        }
        
        $newRes->whatsapp_message_id = json_encode($messageId);
        $newRes->update();
        

        $this->send_mail($newRes);

        $mx = $this->save_message([        
            'wa_id' => $newRes->whatsapp_message_id,
            'type_1' => $type_m_1,
            'type_2' => $type_m_2,
            'source' => config('configurazione.db'),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Successo',
            'source' => config('configurazione.db'),
            'data' => $mx,
        ]);
    }

    protected function save_message($data_am1){
        $config = [
            'driver'    => 'mysql',
            'host'      => '127.0.0.1',
            'port'      => '3306',
            'database'  => 'dciludls_demo4',
            'username'  => 'dciludls_ceo',
            'password'  => config('configurazione.MSC_P'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];
    
        DB::purge('dynamic'); // resetta eventuali connessioni precedenti con lo stesso nome
        config(['database.connections.dynamic' => $config]);
    
    
        $now = Carbon::now(); // data e ora corrente
        $source = DB::connection('dynamic')->table('sources')->where('db_name', config('configurazione.db'))->first();
        
        if (!$source) {
            DB::connection('dynamic')
            ->table('sources')
            ->insert(
                [
                    'db_name' => config('configurazione.db'),
                    'username'=> config('configurazione.us'),
                    'token'   => config('configurazione.pw'),
                    'host'    => config('configurazione.hs'),
                    'app_name'=> config('configurazione.APP_NAME'),
                    'app_domain'=> config('configurazione.domain'),
                    'app_url'=> config('configurazione.APP_URL'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
                );
            $source = DB::connection('dynamic')->table('sources')->where('db_name', config('configurazione.db'))->first();
        }
        // Decodifica wa_id e verifica se è valido
        $mex = json_decode($data_am1['wa_id'], true);
        if (!is_array($mex)) {
            return response()->json(['success' => false, 'error' => 'Si è verificato un errore. Riprova più tardi.']);
        }

        Log::info("wa_id decodificato con successo:", ['wa_id' => $mex]);
    
        $i = 1;
        foreach ($mex as $id) {
            DB::connection('dynamic')
            ->table('messages')
            ->insert(
                [
                    'wa_id'  =>  $id,
                    'type'   =>  $i == 1 ? $data_am1['type_1'] : $data_am1['type_2'],
                    'source' =>  $source->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $i++;
        }
        return [$source, $mex];
        
    }
    protected function send_mail($newRes){
        try{
            // Ottieni le impostazioni di contatto
            $adv_s = Setting::where('name', 'advanced')->first();
            $property_adv = json_decode($adv_s->property, 1);
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
                'sala' => $newRes->sala,
                'phone' => $newRes->phone,
                'admin_phone' => $telefono,
                'n_person' => $newRes->n_person,
                'status' => $newRes->status,
                'whatsapp_message_id' => $newRes->whatsapp_message_id,
                'property_adv' => $property_adv,
            ];

            // Invia le email
            $mailAdmin = new confermaOrdineAdmin($bodymail);
            Mail::to(config('configurazione.mf'))->send($mailAdmin);

            $bodymail['to'] = 'user';
            $bodymail['whatsapp_message_id'] = $newRes->whatsapp_message_id;
            $bodymail['title'] = 'Ciao ' . $newRes->name . ', grazie per aver prenotato tramite il nostro sito web';
            $bodymail['subtitle'] = 'La tua prenotazione è nella nostra coda, a breve riceverai l\'esito del processamento';
            
            $mail = new confermaOrdineAdmin($bodymail);
            Mail::to($newRes->email)->send($mail);
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
                }else{
                    return false; // Se la data non è impostata, considera che non è entro 24 ore
                }
            }else{
                 // Controlla se 'last_response_wa' è impostato
                if (isset($property['last_response_wa_2']) && !empty($property['last_response_wa_2'])) {
                    // Confronta la data salvata con le ultime 24 ore
                    $lastResponseDate = Carbon::parse($property['last_response_wa_2']);
                    return $lastResponseDate->greaterThanOrEqualTo(Carbon::now()->subHours(24));
                }else{
                    return false; // Se la data non è impostata, considera che non è entro 24 ore
                }
            }
        }else{
            return false; // Se il record non esiste o la data non è impostata
        }
    }


}