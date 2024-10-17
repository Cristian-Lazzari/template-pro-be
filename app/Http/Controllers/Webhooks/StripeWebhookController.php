<?php

namespace App\Http\Controllers\Webhooks;

use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Date;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class StripeWebhookController extends Controller
{   
    public function handleStripeWebhook(Request $request)
    {
        // La tua chiave segreta di Stripe
        $stripeSecretKey = config('configurazione.STRIPE_SECRET'); 
        
        Log::warning(" SESSIONE CONTROLLER");
        // Imposta la chiave segreta di Stripe
        Stripe::setApiKey($stripeSecretKey);

        // Ottieni il corpo della richiesta
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Il tuo endpoint segreto (ottenuto quando configuri il webhook)
        $endpointSecret = config('configurazione.STRIPE_WEBHOOK_SECRET'); // Inserisci il tuo endpoint segreto

        try {
            // Verifica il webhook
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (UnexpectedValueException $e) {
            // Il payload non è valido
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // La firma non è valida
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Gestisci gli eventi pertinenti
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object; // contiene i dettagli della sessione
                $this->handleCheckoutSessionCompleted($session);
                break;
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contiene i dettagli del pagamento
                $this->handlePaymentIntentSucceeded($paymentIntent);
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object; // contiene i dettagli del pagamento
                $this->handlePaymentIntentFailed($paymentIntent);
                break;
            // Aggiungi altri eventi se necessario
            default:
                Log::warning("Unhandled event type: {$event->type}");
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function handleCheckoutSessionCompleted($session)
    {
        return 'success';
    }

    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        

        // Aggiorna il tuo database per segnare l'ordine come completato
        $orderId = $paymentIntent->metadata->order_id; // Assicurati di aver aggiunto l'ID dell'ordine nei metadata
        // Esegui la logica per aggiornare lo stato dell'ordine nel database
        $order = Order::where('id', $orderId)->with('products')->firstOrFail();
        $date = Date::where('date_slot', $order->date_slot)->firstOrFail();

        $vis = json_decode($date->visible, true);
        $av = json_decode($date->availability, true);
        $res = json_decode($date->reserving, true);

        $arrvar = str_replace('\\', '', $order->cart);
        $cart = json_decode($arrvar, true);
        // aggiorno la disponibilità in date
        if(config('configurazione.typeOfOrdering')){
            $res_c1 = $res['cucina_1'];
            $res_c2 = $res['cucina_2'];
            $av_c1  = $av['cucina_1'];
            $av_c2  = $av['cucina_2'];
            // Inizializza i contatori
            $np_c1  = 0;
            $np_c2  = 0;
            // Cicla sui prodotti associati all'ordine
            foreach ($order->products as $product) {
                // Controlla il tipo di cucina del prodotto
                if ($product->type_slot == 1) {
                    $np_c1++;
                } elseif ($product->type_slot == 2) {
                    $np_c2++;
                }
            }
            if(isset($order->comune)){
                if( ($res['domicilio'] + 1) < $av['domicilio']){
                    $res['domicilio'] = $res['domicilio'] + 1;
                } elseif (($res['domicilio'] + 1) == $av['domicilio']){
                    $res['domicilio'] = $res['domicilio'] + 1;
                    $vis['domicilio'] = 0;
                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata,  ci dispiace per l\'inconveniente... provate di nuovo',
                        'data' => $date
                    ]);
                }
            }

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

            $date->visible = json_encode($vis);
            $date->reserving = json_encode($res);
        }else{
            if(isset($order->comune)){
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
        
        $order->status = 3;
        $date->update();
        $order->update();
        $set = Setting::where('name', 'Contatti')->firstOrFail();
        $p_set = json_decode($set->property, true);
        
        $bodymail_a = [
            'type' => 'or',
            'to' => 'admin',

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
        $bodymail_u = [
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
        return 'success';
        // $mail = new confermaOrdineAdmin($bodymail_u);
        // Mail::to($data['email'])->send($mail);

        // $mailAdmin = new confermaOrdineAdmin($bodymail_a);
        // Mail::to(config('configurazione.mail'))->send($mailAdmin);
        
    }

    protected function handlePaymentIntentFailed($paymentIntent)
    {
        // Aggiorna il tuo database per segnare l'ordine come fallito
        $orderId = $paymentIntent->metadata->order_id; // Assicurati di aver aggiunto l'ID dell'ordine nei metadata
        // Esegui la logica per aggiornare lo stato dell'ordine nel database
        $orderId = $session->metadata->order_id; // Assicurati di aver aggiunto l'ID dell'ordine nei metadata
        // Esegui la logica per aggiornare lo stato dell'ordine nel database
        $order = Order::where('id', $orderId)->firstOrFail();
        $order->status = 0;
        $order->update();
    
    }

}
