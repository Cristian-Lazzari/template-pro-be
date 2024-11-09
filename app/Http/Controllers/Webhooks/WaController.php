<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WaController extends Controller
{
    // Metodo per gestire la verifica del webhook
    public function verify(Request $request)
    {
        //$verifyToken = config('configurazione.WA_TO');
        $verifyToken = 'ciao1234qwqwqwqwmqwjqwjj32j23i2h32iu3hu';

        if ($request->query('hub_verify_token') === $verifyToken) {
            return response($request->query('hub_challenge'), 200);
        }

        return response('Token di verifica non valido', 403);
    }

    // Metodo per gestire i webhook
    public function handle(Request $request)
    {
        $data = $request->all();
        Log::warning(" SESSIONE CONTROLLER");

        // Controlla se il webhook è un evento di risposta con un ID messaggio
        if (isset($data['message']) && isset($data['message']['interactive'])) {
            $buttonText = $data['message']['interactive']['button_reply']['title']; // Testo del pulsante premuto
            $messageId = $data['message']['id']; // ID del messaggio ricevuto

            // Trova l'ordine corrispondente tramite l'ID del messaggio
            $order = Order::where('whatsapp_message_id', $messageId)->first();

            if ($order) {
                if ($buttonText === 'Conferma') {
                    // Aggiorna lo stato dell'ordine a "Confermato"
                    $order->status = 5;
                } elseif ($buttonText === 'Annulla') {
                    // Aggiorna lo stato dell'ordine a "Annullato"
                    $order->status = 0;
                }

                $order->save();
            }
        }

        return response()->json(['status' => 'success']);
    }


}
