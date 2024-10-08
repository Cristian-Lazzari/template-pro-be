<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class PaymentController extends Controller
{
    public function checkout($cart) 
    {

        $YOUR_DOMAIN = 'http://localhost:8000';

        $stripe = new \Stripe\StripeClient(config('configurazione.secret_stripe'));
 
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
            'success_url' => 'http://localhost:5173/',
            'cancel_url' => 'http://localhost:8000/',
        ]);
        
        return redirect()->away($checkout_session->url);

    }

}