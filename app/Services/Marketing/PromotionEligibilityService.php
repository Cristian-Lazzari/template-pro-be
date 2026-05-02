<?php

namespace App\Services\Marketing;

use App\Models\Customer;
use App\Models\Promotion;

class PromotionEligibilityService
{
    public function canBeAssignedToCustomer(Promotion $promotion, Customer $customer): bool
    {
        return $this->getFailureReason($promotion, $customer) === null;
    }

    public function canBeUsedByCustomer(
        Promotion $promotion,
        Customer $customer,
        ?float $amount = null,
        ?string $caseUse = null
    ): bool {
        return $this->getFailureReason($promotion, $customer, $amount, $caseUse) === null;
    }

    public function getFailureReason(
        Promotion $promotion,
        Customer $customer,
        ?float $amount = null,
        ?string $caseUse = null
    ): ?string {
        if (! $promotion->isActive()) {
            return 'Promotion is not active.';
        }

        if (! $promotion->isPermanent()) {
            if ($promotion->schedule_at && $promotion->schedule_at->isFuture()) {
                return 'Promotion is not started yet.';
            }

            if ($promotion->expiring_at && $promotion->expiring_at->isPast()) {
                return 'Promotion is expired.';
            }
        }

        if ($amount !== null && $promotion->minimum_pretest !== null && $amount < (float) $promotion->minimum_pretest) {
            return 'Promotion minimum amount has not been reached.';
        }

        if ($caseUse !== null && filled($promotion->case_use) && $this->normalizeCaseUse($promotion->case_use) !== $this->normalizeCaseUse($caseUse)) {
            return 'Promotion case use does not match.';
        }

        if (! $promotion->isReusable() && $this->customerAlreadyUsedPromotion($promotion, $customer)) {
            return 'Promotion has already been used by this customer.';
        }

        return null;
    }

    private function customerAlreadyUsedPromotion(Promotion $promotion, Customer $customer): bool
    {
        if (! $customer->exists || ! $promotion->exists) {
            return false;
        }

        return $customer->customerPromotions()
            ->where('promotion_id', $promotion->getKey())
            ->whereNotNull('promo_used')
            ->exists();
    }

    private function normalizeCaseUse(string $caseUse): string
    {
        return mb_strtolower(trim($caseUse));
    }
}
