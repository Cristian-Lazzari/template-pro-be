<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use App\Models\Order;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{        
    public function checkout($cart, $id) 
    {
        
        $YOUR_DOMAIN = 'http://127.0.0.1:8000/';
        $final_destination = config('configurazione.domain') . '/success-pay'; 
        $final_destination_error = config('configurazione.domain') . '/error-pay'; 
        $stripeSecretKey = config('configurazione.STRIPE_SECRET'); 

        $stripe = new \Stripe\StripeClient($stripeSecretKey);
 
        $line_items = [];
        foreach ($cart as $item) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => $item['price'],
                ],
                'quantity' => $item['counter'],
            ];
        }

        $checkout_session = $stripe->checkout->sessions->create([
            'line_items' => $line_items,
            'mode' => 'payment',
            'metadata' => [
                'order_id' => $id,
            ],
            'success_url' => $final_destination,
            'cancel_url' => $final_destination_error,
        ]);
        
        // Qui puoi salvare l'ID della sessione nel database
        $checkoutSessionId = $checkout_session->id;

        $order = Order::where('id', $id)->firstOrFail();
        if ($order) {
            $order->checkout_session_id = $checkoutSessionId; // Salva l'ID della sessione
            $order->update();
        }
        
        return $checkout_session->url;

    }

}