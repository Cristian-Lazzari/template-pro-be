<?php

namespace App\Http\Controllers\Api;

use App\Models\Date;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use App\Http\Controllers\Controller;
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
            
            $date->update();
            $newRes->save();

            // Ottieni le impostazioni di contatto
            $set = Setting::where('name', 'Contatti')->firstOrFail();
            if(isset($p_set['telefono'])){
                $telefono = $p_set['telefono'];
            }else{
                $telefono = '3332222333';
            }
            $p_set = json_decode($set->property, true);

            // Prepara i dati per le email
            $bodymail_a = [
                'type' => 'res',
                'to' => 'admin',
                'name' => $newRes->name,
                'surname' => $newRes->surname,
                'email' => $newRes->email,
                'date_slot' => $newRes->date_slot,
                'message' => $newRes->message,
                'phone' => $newRes->phone,
                'admin_phone' => $telefono,
                'n_person' => $n_person,
                'status' => $newRes->status,
            ];
            $bodymail_u = [
                'type' => 'res',
                'to' => 'user',
                'name' => $newRes->name,
                'surname' => $newRes->surname,
                'email' => $newRes->email,
                'date_slot' => $newRes->date_slot,
                'message' => $newRes->message,
                'phone' => $newRes->phone,
                'admin_phone' => $telefono,
                'n_person' => $n_person,
                'status' => $newRes->status,
            ];

            // Invia le email
            try {
                $mail = new confermaOrdineAdmin($bodymail_u);
                Mail::to($newRes->email)->send($mail);
                
                $mailAdmin = new confermaOrdineAdmin($bodymail_a);
                Mail::to(config('configurazione.mail'))->send($mailAdmin);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errore durante l\'invio delle email: ' . $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 200);
            }

            // Risposta di successo
            return response()->json([
                'success' => true,
                'prenotazione' => $newRes,
                'data' => $date
            ]);

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

}