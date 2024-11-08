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
        Log::info('Webhook ricevuto: ', $request->all());

        $entry = $request->input('entry')[0] ?? [];
        $changes = $entry['changes'][0] ?? [];
        $value = $changes['value'] ?? [];
        $messages = $value['messages'][0] ?? [];

        // Verifica se è un messaggio interattivo con pulsante
        if (isset($messages['type']) && $messages['type'] === 'button') {
            $buttonResponse = $messages['interactive']['button_reply']['id'] ?? null;

            // Azione in base alla risposta del pulsante
            if ($buttonResponse === 'confirm_button') {
                // Esegui azione per conferma
                Log::info('L’utente ha cliccato su: Confermo');
                // Logica per la conferma, ad esempio aggiornare il database o inviare un messaggio di conferma
            } elseif ($buttonResponse === 'cancel_button') {
                // Esegui azione per annullamento
                Log::info('L’utente ha cliccato su: Annulla');
                // Logica per l'annullamento, come inviare una notifica o annullare una prenotazione
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
