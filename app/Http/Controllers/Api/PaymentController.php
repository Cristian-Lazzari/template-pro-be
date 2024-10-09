<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function checkout($cart) 
    {
        
        $YOUR_DOMAIN = 'http://localhost:8000';

        $stripe = new \Stripe\StripeClient(config('configurazione.secret_stripe'));

        $checkout_session = $stripe->checkout->sessions->create([
             'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'T-shirt',
                    ],
                    'unit_amount' => 9000,
                ],
                'quantity' => 1,
            ],
            [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Jeans',
                    ],
                    'unit_amount' => 300,
                ],
                'quantity' => 2,
            ]
        ], 
            'mode' => 'payment',
            'success_url' => 'http://localhost:5174/',
            'cancel_url' => 'http://localhost:8000/',
        ]);
        Log::info( 'log');

        
        return redirect()->away($checkout_session->url);

    }

    // public function checkout($cart) 
    // {
        
    //     $YOUR_DOMAIN = 'http://localhost:8000';

    //     $stripe = new \Stripe\StripeClient(config('configurazione.secret_stripe'));
 
    //     $line_items = [];
    //     foreach ($cart as $item) {
    //         $line_items[] = [
    //             'price_data' => [
    //                 'currency' => 'eur',
    //                 'product_data' => [
    //                     'name' => $item['name'],
    //                 ],
    //                 'unit_amount' => $item['price'],
    //             ],
    //             'quantity' => $item['counter'],
    //         ];
    //     }

    //     $checkout_session = $stripe->checkout->sessions->create([
    //         'line_items' => $line_items,
    //         'mode' => 'payment',
    //         'success_url' => 'http://localhost:5173/',
    //         'cancel_url' => 'http://localhost:8000/',
    //     ]);
    //     Log::info($line_items);

        
    //     return redirect()->away($checkout_session->url);

    // }

}