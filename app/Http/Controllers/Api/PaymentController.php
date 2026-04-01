<?php

namespace App\Http\Controllers\Api;

use Stripe\Stripe;
use App\Models\Order;
use App\Models\Setting;
use Stripe\PaymentIntent;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{        
    public function checkout($cart, $id, $delivery, $menus) 
    {
       
        $final_destination = config('configurazione.domain') . '/success-pay'; 
        $final_destination_error = config('configurazione.domain') . '/error-pay'; 
        $stripeSecretKey = config('configurazione.STRIPE_SECRET'); 

        $stripe = new \Stripe\StripeClient($stripeSecretKey);
 
        $line_items = [];
        foreach ($menus as $menu) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $menu->name,
                    ],
                    'unit_amount' => $menu->price, 
                ],
                'quantity' => $menu->pivot->quantity,
            ];
            if ($menu->fixed_menu == '2') {

                $choices = json_decode($menu->pivot->choices, 1);
                foreach ($choices as $key => $value) {
                    foreach ($menu->products as $p) {
                        if ($p->id == $value) {
                            $line_items[] = [
                                'price_data' => [
                                    'currency' => 'eur',
                                    'product_data' => [
                                        'name' => $p->name,
                                    ],
                                    'unit_amount' =>  $p->pivot->extra_price ? $p->pivot->extra_price : 0, 
                                ],
                                'quantity' => $menu->pivot->quantity,
                            ];
                            break; 
                        }
                    }
                }
            }

        }
        foreach ($cart as $orderProduct) {
            //return [$orderProduct->quantity, $orderProduct->price];
            $line_items[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $orderProduct->name,
                    ],
                    'unit_amount' => $orderProduct->price, // Prezzo totale del prodotto senza gli extra
                ],
                'quantity' => $orderProduct->pivot->quantity,
            ];

            // Calcola i prezzi degli ingredienti aggiunti
            $added_ingredients = json_decode($orderProduct->pivot->add, true); // Decodifica la stringa add
            $option_ingredients = json_decode($orderProduct->pivot->option, true); // Decodifica la stringa option
            $removed_ingredients = json_decode($orderProduct->pivot->remove, true); // Decodifica la stringa remove



            // Aggiungi prezzi ingredienti 'add'
            if ($added_ingredients) {
                foreach ($added_ingredients as $ingredient_name) {
                    $ingredient = Ingredient::where('name', $ingredient_name)->first();
                    if ($ingredient) {
                        $line_items[] = [
                            'price_data' => [
                                'currency' => 'eur',
                                'product_data' => [
                                    'name' => $ingredient_name . '(EXTRA)',
                                ],
                                'unit_amount' => $ingredient->price, 
                            ],
                            'quantity' => $orderProduct->pivot->quantity,
                        ];
                        
                    }
                }
            }

            // Aggiungi prezzi ingredienti 'option'
            if ($option_ingredients) {      
                foreach ($option_ingredients as $option_name) {
                    $option = Ingredient::where('name', $option_name)->first();
                    if ($option) {
                        $line_items[] = [
                            'price_data' => [
                                'currency' => 'eur',
                                'product_data' => [
                                    'name' => $option_name . '(EXTRA)',
                                ],
                                'unit_amount' => $option->price, 
                            ],
                            'quantity' => $orderProduct->pivot->quantity,
                        ];
                        

                    }
                }
            }
            

            if ($removed_ingredients) {
                foreach ($removed_ingredients as $option_name) {
                    $option = Ingredient::where('name', $option_name)->first();
                    if ($option) {
                        $line_items[] = [
                            'price_data' => [
                                'currency' => 'eur',
                                'product_data' => [
                                    'name' => $option_name . '(RIMOSSO)',
                                ],
                                'unit_amount' => 0, 
                            ],
                            'quantity' => $orderProduct->pivot->quantity,
                        ];

                    }
                }
            }
        }
        if($delivery){
            $line_items[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Spese di spedizione',
                    ],
                    'unit_amount' => $delivery, // Costo di spedizione in centesimi
                ],
                'quantity' => 1,
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

        
        return $checkout_session->url;

    }

}