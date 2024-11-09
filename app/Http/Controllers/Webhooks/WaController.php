<?php

namespace App\Http\Controllers\Webhooks;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

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
            $messageId = $message['id']; // ID del messaggio ricevuto

            Log::warning("Pulsante premuto: $buttonText, ID messaggio: $messageId");

            // Trova l'ordine corrispondente tramite l'ID del messaggio
            $order = Order::where('whatsapp_message_id', $messageId)->firstOrFail();

            if ($order) {
                Log::warning("Pulsante premuto: $buttonText, ID messaggio: $messageId, $order");

                if ($buttonText === 'Conferma') {
                    // Aggiorna lo stato dell'ordine a "Confermato"
                    $order->status = 5;
                } elseif ($buttonText === 'Annulla') {
                    // Aggiorna lo stato dell'ordine a "Annullato"
                    $order->status = 0;
                }

                $order->update();
            }
        } else {
            //Log::warning("Nessun pulsante trovato nel messaggio interattivo.");
        }
    } else {
        //Log::warning("Struttura del messaggio non valida o messaggio mancante.");
    }

    return response()->json(['status' => 'success']);
}



}
