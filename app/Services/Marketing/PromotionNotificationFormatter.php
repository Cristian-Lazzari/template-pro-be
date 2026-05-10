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

    public function annotationsForOrder(Order $order): array
    {
        $annotations = [];

        foreach ($this->forOrder($order) as $promotion) {
            foreach ($promotion['affected_items'] ?? [] as $affectedItem) {
                $type = (string) ($affectedItem['type'] ?? '');
                $id = $affectedItem['id'] ?? null;

                if ($type === '' || $id === null) {
                    continue;
                }

                $annotation = $this->annotationLabel($promotion, $affectedItem);

                if ($annotation === null) {
                    continue;
                }

                $baseKey = $this->annotationKey($type, $id);
                $annotations[$baseKey] ??= $annotation;

                if (array_key_exists('cart_index', $affectedItem) && $affectedItem['cart_index'] !== null) {
                    $annotations[$this->annotationKey($type, $id, (int) $affectedItem['cart_index'])] = $annotation;
                }
            }
        }

        return $annotations;
    }

    public function annotationForItem(array $annotations, string $type, int $id, ?int $cartIndex = null): ?string
    {
        if ($cartIndex !== null) {
            $cartKey = $this->annotationKey($type, $id, $cartIndex);

            if (isset($annotations[$cartKey])) {
                return $annotations[$cartKey];
            }
        }

        return $annotations[$this->annotationKey($type, $id)] ?? null;
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
        $affectedItems = $this->affectedItems($customerPromotion, $promotionName, $typeDiscount, $promotion?->discount);

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

    private function affectedItems(
        CustomerPromotion $customerPromotion,
        string $promotionName,
        string $typeDiscount,
        mixed $promotionDiscount
    ): array {
        $metadata = $customerPromotion->metadata;

        if (! is_array($metadata)) {
            return [];
        }

        $affectedItems = $metadata['affected_items'] ?? [];

        if (! is_array($affectedItems)) {
            return [];
        }

        return collect($affectedItems)
            ->filter(fn ($item) => is_array($item))
            ->map(function (array $item) use ($promotionName, $typeDiscount, $promotionDiscount) {
                $item['promotion_name'] ??= $promotionName;
                $item['type_discount'] ??= $typeDiscount;
                $item['discount_label'] ??= $this->discountLabel($typeDiscount, $promotionDiscount, $item['discount_amount'] ?? null);

                return $item;
            })
            ->values()
            ->all();
    }

    private function annotationLabel(array $promotion, array $affectedItem): ?string
    {
        $typeDiscount = (string) ($affectedItem['type_discount'] ?? ($promotion['type_discount'] ?? ''));

        if ($typeDiscount === 'gift' || ! empty($affectedItem['gift_quantity'])) {
            return '🎁 Omaggio';
        }

        if ($typeDiscount === 'percentage') {
            $label = $affectedItem['discount_label'] ?? null;

            if (is_string($label) && str_contains($label, '%')) {
                return '🎁 con promozione ' . $this->ensureNegativeLabel($label);
            }

            return '🎁 con promozione';
        }

        $discountAmount = (float) ($affectedItem['discount_amount'] ?? 0);

        if ($discountAmount > 0) {
            return '🎁 con promozione -' . Currency::formatCents($discountAmount);
        }

        $label = $affectedItem['discount_label'] ?? null;

        if (is_string($label) && filled($label)) {
            return '🎁 con promozione ' . $this->ensureNegativeLabel($label);
        }

        return '🎁 con promozione';
    }

    private function annotationKey(string $type, mixed $id, ?int $cartIndex = null): string
    {
        $key = $type . ':' . $id;

        return $cartIndex === null ? $key : $key . ':' . $cartIndex;
    }

    private function discountLabel(string $typeDiscount, mixed $promotionDiscount, mixed $itemDiscount = null): ?string
    {
        if ($typeDiscount === 'gift') {
            return 'Omaggio';
        }

        if ($typeDiscount === 'percentage' && $promotionDiscount !== null) {
            return '-' . rtrim(rtrim(number_format((float) $promotionDiscount, 2, ',', ''), '0'), ',') . '%';
        }

        $discount = $itemDiscount ?? $promotionDiscount;

        if ($typeDiscount === 'fixed' && $discount !== null && (float) $discount > 0) {
            return '-' . Currency::formatCents((float) $discount);
        }

        return null;
    }

    private function ensureNegativeLabel(string $label): string
    {
        $label = trim($label);

        return str_starts_with($label, '-') ? $label : '-' . $label;
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
