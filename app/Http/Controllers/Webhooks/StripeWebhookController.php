<?php

namespace App\Http\Controllers\Webhooks;

use Stripe\Stripe;
use Stripe\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class StripeWebhookController extends Controller
{
    
   
    
    public function handleStripeWebhook(Request $request)
    {
        return dump('ciao');
    }
    // {
    //     // La tua chiave segreta di Stripe
    //     $stripeSecretKey = config('configurazione.STRIPE_SECRET'); 
        
    //     Log::warning(" SESSIONE CONTROLLER");
    //     // Imposta la chiave segreta di Stripe
    //     Stripe::setApiKey($stripeSecretKey);

    //     // Ottieni il corpo della richiesta
    //     $payload = $request->getContent();
    //     $sigHeader = $request->header('Stripe-Signature');

    //     // Il tuo endpoint segreto (ottenuto quando configuri il webhook)
    //     $endpointSecret = config('configurazione.STRIPE_WEBHOOK_SECRET'); // Inserisci il tuo endpoint segreto

    //     try {
    //         // Verifica il webhook
    //         $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
    //     } catch (UnexpectedValueException $e) {
    //         // Il payload non è valido
    //         return response()->json(['error' => 'Invalid payload'], 400);
    //     } catch (\Stripe\Exception\SignatureVerificationException $e) {
    //         // La firma non è valida
    //         return response()->json(['error' => 'Invalid signature'], 400);
    //     }

    //     // Gestisci gli eventi pertinenti
    //     switch ($event->type) {
    //         case 'checkout.session.completed':
    //             $session = $event->data->object; // contiene i dettagli della sessione
    //             $this->handleCheckoutSessionCompleted($session);
    //             break;
    //         case 'payment_intent.succeeded':
    //             $paymentIntent = $event->data->object; // contiene i dettagli del pagamento
    //             $this->handlePaymentIntentSucceeded($paymentIntent);
    //             break;
    //         case 'payment_intent.payment_failed':
    //             $paymentIntent = $event->data->object; // contiene i dettagli del pagamento
    //             $this->handlePaymentIntentFailed($paymentIntent);
    //             break;
    //         // Aggiungi altri eventi se necessario
    //         default:
    //             Log::warning("Unhandled event type: {$event->type}");
    //     }

    //     return response()->json(['status' => 'success'], 200);
    // }

    protected function handleCheckoutSessionCompleted($session)
    {
        Log::warning(" SESSIONE DI COMPLETAMENT0");
        // Aggiorna il tuo database per segnare l'ordine come completato
        // Assicurati di usare l'ID dell'ordine per aggiornare correttamente il record
        $orderId = $session->metadata->order_id; // Assicurati di aver aggiunto l'ID dell'ordine nei metadata
        // Esegui la logica per aggiornare lo stato dell'ordine nel database
        $order = Order::where('id', $orderId)->firstOrFail();
        $order->status = 3;
        $order->update();
    }

    protected function handlePaymentIntentSucceeded($paymentIntent)
    {

        // Aggiorna il tuo database per segnare l'ordine come completato
        $orderId = $paymentIntent->metadata->order_id; // Assicurati di aver aggiunto l'ID dell'ordine nei metadata
        // Esegui la logica per aggiornare lo stato dell'ordine nel database
        $orderId = $session->metadata->order_id; // Assicurati di aver aggiunto l'ID dell'ordine nei metadata
        // Esegui la logica per aggiornare lo stato dell'ordine nel database
        $order = Order::where('id', $orderId)->firstOrFail();
        $order->status = 3;
        $order->update();
    
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
