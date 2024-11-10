<?php

namespace App\Http\Controllers\Webhooks;

use Stripe\Refund;
use Stripe\Stripe;
use App\Models\Date;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class WaController extends Controller
{
    // Metodo per gestire la verifica del webhook
    public function verify(Request $request)
    {
        //$verifyToken = config('configurazione.WA_TO');
        $verifyToken = 'diocane';

        if ($request->query('hub_verify_token') === $verifyToken) {
            return response($request->query('hub_challenge'), 200);
        }

        return response('Token di verifica non valido', 403);
    }

    // Metodo per gestire i webhook
    public function handle(Request $request)
    {
        $data = $request->all();
        Log::warning("Webhook ricevuto", $data);

        // Naviga nella struttura del webhook
        if (isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
            $message = $data['entry'][0]['changes'][0]['value']['messages'][0];
            

            // Controlla se il messaggio ha un'interazione con pulsante
            if (isset($message['button']) && isset($message['button']['text'])) {
                $buttonText = $message['button']['text']; // Testo del pulsante premuto
                $messageId = $data['entry'][0]['changes'][0]['value']['statuses'][0]['id'] ?? null;

                Log::warning("Pulsante premuto: $buttonText, ID messaggio: $messageId");

                // Trova l'ordine corrispondente tramite l'ID del messaggio
            
                $order_ex = Order::where('whatsapp_message_id', $messageId)->exists();
                
                if ($order_ex) {
                    $order = Order::where('whatsapp_message_id', $messageId)->first();
                    Log::info("Ordine trovato per il Message ID: " . $messageId);
                    if($buttonText === 'Conferma'){
                        $this->statusOr(1, $order);
                    }elseif($buttonText === 'Annulla'){
                        $this->statusOr(0, $order);
                    }
                } else {
                    // Se non trovato in Orders, cerca nella tabella rervations
                    $reservation = Reservation::where('whatsapp_message_id', $messageId)->first();
                    if ($reservation) {
                        Log::info("Prenotazione trovata per il Message ID: " . $messageId);
                        if($buttonText === 'Conferma'){
                            $this->statusRes(1, $reservation);
                        }elseif($buttonText === 'Annulla'){
                            $this->statusRes(0, $reservation);
                        }
                    } else {
                        // Nessun ordine o prenotazione trovato per il Message ID
                        Log::warning("Nessun ordine o prenotazione trovati per il Message ID: " . $messageId);
                    }
                }
            } else {
                //Log::warning("Nessun pulsante trovato nel messaggio interattivo.");
            }
        } else {
            //Log::warning("Struttura del messaggio non valida o messaggio mancante.");
        }

        return response()->json(['status' => 'success']);
    }

    protected function statusOrder($c_a, $order){
        if($c_a == 1 && in_array($order->status, [1, 5])){
            return;
        }elseif(in_array($order->status, [1, 5])){
            return;
        }
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
                $m = 'L\'ordine erag gia stato annullato!';
                return redirect()->back()->with('success', $m); 
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
        if($c_a == 1 && in_array($res->status, [1, 5])){
            return;
        }elseif(in_array($res->status, [1, 5])){
            return;
        }
        if($c_a){
            $res->status = 1;
            $m = 'La prenotazione e\' stata confermata correttamente';
            $message = 'Siamo felici di informarti che la tua prenotazione e\' stata confermata, ti ricordo la data e l\'orario che hai scelto: ' . $res->date_slot ;
        }else{
            if($res->status == 0){
                $m = 'La prenotazione e\' stata gia annullata correttamente';
                return redirect()->back()->with('success', $m);
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



}
