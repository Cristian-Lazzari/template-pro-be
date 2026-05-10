<?php

namespace App\Services\Marketing;

use App\Models\CustomerPromotion;
use App\Models\Order;
use App\Models\Reservation;
use App\Support\Currency;
use Illuminate\Database\Eloquent\Model;

class PromotionNotificationFormatter
{
    public function forOrder(Order $order): array
    {
        return $this->formatAppliedPromotions($order, 'order_id', $order->getKey(), false);
    }

    public function forReservation(Reservation $reservation): array
    {
        return $this->formatAppliedPromotions($reservation, 'reservation_id', $reservation->getKey(), true);
    }

    public function whatsappTextForOrder(Order $order): ?string
    {
        $promotions = $this->forOrder($order);

        if ($promotions === []) {
            return null;
        }

        $lines = [];

        foreach ($promotions as $promotion) {
            $lines[] = '🎁 Promozione applicata: ' . $promotion['promotion_name'];

            if ($promotion['type_discount'] === 'gift') {
                $lines[] = 'Omaggio applicato';
            } elseif ($promotion['discount_amount'] > 0) {
                $lines[] = 'Sconto: ' . Currency::formatCents($promotion['discount_amount']);
            }

            $lines[] = 'Totale scontato: ' . Currency::formatCents($order->tot_price);
        }

        return implode("\n", $lines);
    }

    public function whatsappTextForReservation(Reservation $reservation): ?string
    {
        $promotions = $this->forReservation($reservation);

        if ($promotions === []) {
            return null;
        }

        $lines = [];

        foreach ($promotions as $promotion) {
            $lines[] = '🎁 Promozione prenotazione: ' . $promotion['promotion_name'];

            if ($promotion['type_discount'] === 'gift') {
                $lines[] = 'Omaggio applicato';
            }
        }

        return implode("\n", $lines);
    }

    private function formatAppliedPromotions(Model $model, string $foreignKey, int $modelId, bool $reservation): array
    {
        $model->loadMissing('customerPromotions.promotion');

        return $model->customerPromotions
            ->filter(fn (CustomerPromotion $customerPromotion) => (int) $customerPromotion->{$foreignKey} === $modelId)
            ->map(fn (CustomerPromotion $customerPromotion) => $this->formatCustomerPromotion($customerPromotion, $reservation))
            ->values()
            ->all();
    }

    private function formatCustomerPromotion(CustomerPromotion $customerPromotion, bool $reservation): array
    {
        $promotion = $customerPromotion->promotion;
        $promotionName = $promotion?->name ?: 'Promozione #' . $customerPromotion->promotion_id;
        $typeDiscount = (string) ($promotion?->type_discount ?? '');
        $discountAmount = (float) ($customerPromotion->discount_amount ?? 0);
        $affectedItems = $this->affectedItems($customerPromotion);

        return [
            'customer_promotion_id' => $customerPromotion->getKey(),
            'promotion_id' => $customerPromotion->promotion_id,
            'promotion_name' => $promotionName,
            'type_discount' => $typeDiscount,
            'type_label' => $this->typeLabel($typeDiscount),
            'discount_amount' => $discountAmount,
            'case_use' => $promotion?->case_use,
            'label' => $this->label($promotionName, $typeDiscount, $discountAmount, $reservation),
            'affected_items' => $affectedItems,
        ];
    }

    private function affectedItems(CustomerPromotion $customerPromotion): array
    {
        $metadata = $customerPromotion->metadata;

        if (! is_array($metadata)) {
            return [];
        }

        $affectedItems = $metadata['affected_items'] ?? [];

        return is_array($affectedItems) ? $affectedItems : [];
    }

    private function label(string $promotionName, string $typeDiscount, float $discountAmount, bool $reservation): string
    {
        if ($reservation) {
            return 'Promozione tavolo attivata: ' . $promotionName;
        }

        if ($typeDiscount === 'gift') {
            return 'Omaggio applicato';
        }

        if ($discountAmount > 0) {
            return 'Sconto applicato: ' . Currency::formatCents($discountAmount);
        }

        return 'Promozione applicata: ' . $promotionName;
    }

    private function typeLabel(string $typeDiscount): string
    {
        return match ($typeDiscount) {
            'fixed' => 'Sconto fisso',
            'percentage' => 'Sconto percentuale',
            'gift' => 'Omaggio',
            default => $typeDiscount !== '' ? $typeDiscount : 'Promozione',
        };
    }
}
