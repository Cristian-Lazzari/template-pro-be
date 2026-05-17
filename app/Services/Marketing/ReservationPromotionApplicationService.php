<?php

namespace App\Services\Marketing;

use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ReservationPromotionApplicationService
{
    private const RESERVATION_CASES = ['generic', 'table'];

    public function __construct(
        private PromotionAvailabilityService $promotionAvailabilityService,
    ) {
    }

    public function evaluate(Customer $customer, ?int $customerPromotionId = null, array $reservationData = []): array
    {
        if ($customerPromotionId === null) {
            return $this->findBestApplicable($customer, $reservationData);
        }

        $customerPromotion = CustomerPromotion::query()
            ->with('promotion')
            ->find($customerPromotionId);

        return $this->evaluateCustomerPromotion($customer, $customerPromotion, $reservationData);
    }

    public function findBestApplicable(Customer $customer, array $reservationData = []): array
    {
        $results = CustomerPromotion::query()
            ->with('promotion')
            ->where('customer_id', $customer->getKey())
            ->whereNull('promo_used')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'used');
            })
            ->get()
            ->map(fn (CustomerPromotion $customerPromotion) => $this->evaluateCustomerPromotion(
                $customer,
                $customerPromotion,
                $reservationData
            ))
            ->filter(fn (array $result) => $result['applicable'])
            ->sortByDesc(fn (array $result) => $result['promotion']?->discount ?? 0)
            ->values();

        if ($results->isEmpty()) {
            return $this->result(false, 'no_applicable_promotion');
        }

        return $results->first();
    }

    private function evaluateCustomerPromotion(
        Customer $customer,
        ?CustomerPromotion $customerPromotion,
        array $reservationData
    ): array {
        if (! $customerPromotion) {
            return $this->result(false, 'customer_promotion_not_found');
        }

        if ((int) $customerPromotion->customer_id !== (int) $customer->getKey()) {
            return $this->result(false, 'customer_promotion_customer_mismatch', $customerPromotion);
        }

        $promotion = $customerPromotion->promotion;

        if (! $promotion) {
            return $this->result(false, 'promotion_not_found', $customerPromotion);
        }

        $failureReason = $this->validateAvailability($customerPromotion, $promotion, $reservationData);

        if ($failureReason !== null) {
            return $this->result(
                false,
                $failureReason['reason'],
                $customerPromotion,
                $promotion,
                $failureReason['affected_items'] ?? []
            );
        }

        return $this->result(
            true,
            null,
            $customerPromotion,
            $promotion,
            [$this->affectedReservationPayload($promotion, $reservationData)]
        );
    }

    private function validateAvailability(
        CustomerPromotion $customerPromotion,
        Promotion $promotion,
        array $reservationData
    ): ?array {
        if ($customerPromotion->status === 'used' || $this->customerPromotionUsedAt($customerPromotion) !== null) {
            return ['reason' => 'customer_promotion_already_used'];
        }

        if ($this->customerPromotionExpiresAt($customerPromotion)?->isPast()) {
            return ['reason' => 'customer_promotion_expired'];
        }

        if (! $promotion->isActive()) {
            return ['reason' => 'promotion_not_active'];
        }

        if ($promotion->schedule_at?->isFuture()) {
            return ['reason' => 'promotion_not_started'];
        }

        if (! $promotion->isPermanent() && $promotion->expiring_at?->isPast()) {
            return ['reason' => 'promotion_expired'];
        }

        if (! $this->isReservationCaseUseCompatible($promotion)) {
            return ['reason' => 'invalid_case_use'];
        }

        $availabilityReason = $this->promotionAvailabilityService->unavailableReason(
            $promotion,
            $reservationData['date_slot'] ?? null
        );

        if ($availabilityReason !== null) {
            return ['reason' => $availabilityReason];
        }

        $minimumRequired = $promotion->minimum_pretest !== null
            ? (float) $promotion->minimum_pretest
            : null;

        if ($minimumRequired !== null) {
            $people = $this->reservationPeople($reservationData);

            if ($people !== null && $people < $minimumRequired) {
                return ['reason' => 'minimum_not_reached'];
            }
        }

        return null;
    }

    private function affectedReservationPayload(Promotion $promotion, array $reservationData): array
    {
        $minimumRequired = $promotion->minimum_pretest !== null
            ? (float) $promotion->minimum_pretest
            : null;
        $people = $this->reservationPeople($reservationData);

        return [
            'type' => 'reservation',
            'case_use' => 'table',
            'date_slot' => $reservationData['date_slot'] ?? null,
            'people' => $people,
            'minimum_required' => $minimumRequired,
            'minimum_checked' => $minimumRequired === null || $people !== null,
            'benefit_type' => $promotion->type_discount ?: null,
            'gift_benefit' => $promotion->type_discount === 'gift',
        ];
    }

    private function isReservationCaseUseCompatible(Promotion $promotion): bool
    {
        $caseUse = $this->normalize((string) ($promotion->case_use ?: 'generic'));

        return in_array($caseUse, self::RESERVATION_CASES, true);
    }

    private function reservationPeople(array $reservationData): ?int
    {
        foreach (['people', 'guests', 'quantity', 'persons'] as $key) {
            if (array_key_exists($key, $reservationData)) {
                $people = (int) $reservationData[$key];

                return $people > 0 ? $people : null;
            }
        }

        if (array_key_exists('n_adult', $reservationData) || array_key_exists('n_child', $reservationData)) {
            $people = (int) ($reservationData['n_adult'] ?? 0) + (int) ($reservationData['n_child'] ?? 0);

            return $people > 0 ? $people : null;
        }

        return null;
    }

    private function customerPromotionUsedAt(CustomerPromotion $customerPromotion): ?Carbon
    {
        return $customerPromotion->promo_used
            ?: $this->metadataDate($customerPromotion, 'redeemed_at')
            ?: $this->metadataDate($customerPromotion, 'used_at');
    }

    private function customerPromotionExpiresAt(CustomerPromotion $customerPromotion): ?Carbon
    {
        if (Schema::hasColumn('customer_promotion', 'expires_at')) {
            $expiresAt = $customerPromotion->getAttribute('expires_at');

            if ($expiresAt) {
                try {
                    return $expiresAt instanceof Carbon ? $expiresAt : Carbon::parse($expiresAt);
                } catch (\Throwable) {
                    return null;
                }
            }
        }

        return $this->metadataDate($customerPromotion, 'expires_at');
    }

    private function metadataDate(CustomerPromotion $customerPromotion, string $key): ?Carbon
    {
        $value = data_get($customerPromotion->metadata, $key);

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function result(
        bool $applicable,
        ?string $reason,
        ?CustomerPromotion $customerPromotion = null,
        ?Promotion $promotion = null,
        array $affectedItems = []
    ): array {
        return [
            'applicable' => $applicable,
            'reason' => $reason,
            'customer_promotion' => $customerPromotion,
            'promotion' => $promotion,
            'discount_amount' => 0.0,
            'affected_items' => $affectedItems,
        ];
    }
}
