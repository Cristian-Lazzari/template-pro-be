<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerPromotion;
use App\Models\Order;
use App\Support\Currency;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function checkout($cart, $id, $delivery, $menus)
    {
        $stripe = $this->stripeClient();
        $order = Order::query()
            ->with(['customerPromotions.promotion'])
            ->findOrFail($id);

        $checkoutSession = $stripe->checkout->sessions->create(
            $this->checkoutSessionPayload($order)
        );

        return $checkoutSession->url;
    }

    protected function checkoutSessionPayload(Order $order): array
    {
        $currency = Currency::stripeCode();
        $amount = $this->stripeAmountForOrder($order, $currency);
        $metadata = $this->stripeMetadataForOrder($order);

        return [
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Ordine ristorante #' . $order->getKey(),
                        ],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'metadata' => $metadata,
            'payment_intent_data' => [
                'metadata' => $metadata,
            ],
            'success_url' => config('configurazione.domain') . '/success-pay',
            'cancel_url' => config('configurazione.domain') . '/error-pay',
        ];
    }

    protected function stripeAmountForOrder(Order $order, string $currency): int
    {
        $amount = Currency::toMinorUnits(max(0, Currency::roundAmount($order->tot_price)));

        if ($amount < $this->stripeMinimumAmount($currency)) {
            throw new InvalidArgumentException('order_total_below_stripe_minimum');
        }

        return $amount;
    }

    protected function stripeMinimumAmount(string $currency): int
    {
        return match (strtolower($currency)) {
            'gbp' => 30,
            default => 50,
        };
    }

    protected function stripeMetadataForOrder(Order $order): array
    {
        $customerPromotion = $this->appliedCustomerPromotion($order);
        $metadata = [
            'order_id' => (string) $order->getKey(),
            'order_total' => $this->metadataAmount($order->tot_price),
        ];

        if (! $customerPromotion) {
            return $metadata;
        }

        $promotionMetadata = is_array($customerPromotion->metadata) ? $customerPromotion->metadata : [];
        $promotion = $customerPromotion->promotion;

        return array_merge($metadata, [
            'customer_promotion_id' => (string) $customerPromotion->getKey(),
            'promotion_id' => (string) ($promotion?->getKey() ?? $customerPromotion->promotion_id),
            'discount_amount' => $this->metadataAmount(
                $customerPromotion->discount_amount
                    ?? ($promotionMetadata['discount_amount'] ?? 0)
            ),
            'subtotal_before_discount' => $this->metadataAmount(
                $promotionMetadata['subtotal_before_discount'] ?? null
            ),
            'total_after_discount' => $this->metadataAmount(
                $promotionMetadata['total_after_discount'] ?? $order->tot_price
            ),
        ]);
    }

    protected function appliedCustomerPromotion(Order $order): ?CustomerPromotion
    {
        $promotions = $order->relationLoaded('customerPromotions')
            ? $order->customerPromotions
            : $order->customerPromotions()->with('promotion')->get();

        return $promotions
            ->filter(fn (CustomerPromotion $customerPromotion) => $customerPromotion->order_id !== null)
            ->sortByDesc(fn (CustomerPromotion $customerPromotion) => $customerPromotion->promo_used?->getTimestamp() ?? 0)
            ->first();
    }

    protected function metadataAmount($amount): string
    {
        return number_format(Currency::roundAmount($amount ?? 0), Currency::decimals(), '.', '');
    }

    protected function stripeClient(): \Stripe\StripeClient
    {
        return new \Stripe\StripeClient(config('configurazione.STRIPE_SECRET'));
    }
}
