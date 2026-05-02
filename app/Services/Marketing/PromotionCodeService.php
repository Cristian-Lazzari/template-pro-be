<?php

namespace App\Services\Marketing;

use App\Models\Customer;
use App\Models\Promotion;
use Illuminate\Support\Facades\Schema;

class PromotionCodeService
{
    public function __construct(private PromotionEligibilityService $eligibilityService)
    {
    }

    public function findByCodeOrSlug(string $code): ?Promotion
    {
        $code = trim($code);

        if ($code === '') {
            return null;
        }

        if ($this->promotionHasCodeColumn()) {
            $promotion = Promotion::query()
                ->where('code', $code)
                ->first();

            if ($promotion) {
                return $promotion;
            }
        }

        return Promotion::query()
            ->where('slug', $code)
            ->first();
    }

    public function validateForCustomer(
        string $code,
        Customer $customer,
        ?float $amount = null,
        ?string $caseUse = null
    ): array {
        $promotion = $this->findByCodeOrSlug($code);

        if (! $promotion) {
            return [
                'valid' => false,
                'promotion' => null,
                'reason' => 'Promotion code not found.',
                'discount_type' => null,
                'discount' => null,
            ];
        }

        $failureReason = $this->eligibilityService->getFailureReason($promotion, $customer, $amount, $caseUse);

        return [
            'valid' => $failureReason === null,
            'promotion' => $promotion,
            'reason' => $failureReason,
            'discount_type' => $promotion->type_discount,
            'discount' => $promotion->discount !== null ? (float) $promotion->discount : null,
        ];
    }

    private function promotionHasCodeColumn(): bool
    {
        return Schema::hasTable('promotions') && Schema::hasColumn('promotions', 'code');
    }
}
