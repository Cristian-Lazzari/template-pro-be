<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Http\Controllers\Controller;


class PaymentController extends Controller
{
    public function checkout()
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
            ]],
            'mode' => 'payment',
            'success_url' => 'http://localhost:5173/',
            'cancel_url' => 'http://localhost:8000/',
        ]);
     
        return redirect()->away($checkout_session->url);

    }

    public function processPayment(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => 2000, // L'importo in centesimi (es. 20.00 EUR = 2000)
                'currency' => 'eur',
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}