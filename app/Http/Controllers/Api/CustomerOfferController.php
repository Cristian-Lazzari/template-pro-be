<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerOfferController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            abort(403);
        }

        $offers = [
            'available' => [],
            'used' => [],
            'expired' => [],
        ];

        $customer->customerPromotions()
            ->with(['promotion.targets'])
            ->latest('created_at')
            ->get()
            ->each(function (CustomerPromotion $customerPromotion) use (&$offers) {
                if (! $customerPromotion->promotion) {
                    return;
                }

                $offer = $this->offerPayload($customerPromotion);
                $offers[$offer['status']][] = $offer;
            });

        return response()->json([
            'success' => true,
            ...$offers,
        ]);
    }

    private function offerPayload(CustomerPromotion $customerPromotion): array
    {
        $promotion = $customerPromotion->promotion;
        $usedAt = $this->usedAt($customerPromotion);
        $expiresAt = $this->expiresAt($customerPromotion, $promotion);
        $status = $this->offerStatus($usedAt, $expiresAt);
        $target = $this->primaryTargetPayload($promotion);

        return [
            'id' => $customerPromotion->getKey(),
            'promotion_id' => $promotion->getKey(),
            'name' => $promotion->name,
            'title' => $promotion->name,
            'description' => $this->promotionDescription($promotion),
            'discount_label' => $this->discountLabel($promotion),
            'case_use' => $promotion->case_use ?: 'generic',
            'status' => $status,
            'assignment_status' => $customerPromotion->status,
            'expires_at' => $expiresAt?->toISOString(),
            'used_at' => $usedAt?->toISOString(),
            'redeemed_at' => $usedAt?->toISOString(),
            'cta_path' => $this->ctaPath($promotion),
            'cta_label' => $this->ctaLabel($promotion),
            'target_type' => $target['target_type'],
            'target_name' => $target['target_name'],
            'product_name' => $target['product_name'],
            'menu_name' => $target['menu_name'],
            'category_name' => $target['category_name'],
        ];
    }

    private function offerStatus(?Carbon $usedAt, ?Carbon $expiresAt): string
    {
        if ($usedAt) {
            return 'used';
        }

        if ($expiresAt?->isPast()) {
            return 'expired';
        }

        return 'available';
    }

    private function usedAt(CustomerPromotion $customerPromotion): ?Carbon
    {
        return $customerPromotion->promo_used
            ?: $this->metadataDate($customerPromotion, 'redeemed_at')
            ?: $this->metadataDate($customerPromotion, 'used_at');
    }

    private function expiresAt(CustomerPromotion $customerPromotion, Promotion $promotion): ?Carbon
    {
        return $this->metadataDate($customerPromotion, 'expires_at')
            ?: $promotion->expiring_at;
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

    private function promotionDescription(Promotion $promotion): ?string
    {
        foreach (['description', 'subtitle', 'copy', 'body'] as $key) {
            $value = data_get($promotion->metadata, $key);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function discountLabel(Promotion $promotion): string
    {
        if ($promotion->type_discount === 'gift') {
            return 'Omaggio';
        }

        if ($promotion->discount === null) {
            return 'Offerta riservata';
        }

        $value = number_format((float) $promotion->discount, 2, ',', '.');
        $value = str_ends_with($value, ',00') ? substr($value, 0, -3) : $value;

        return match ($promotion->type_discount) {
            'fixed' => $value . '€',
            'percentage' => $value . '%',
            default => $value,
        };
    }

    private function ctaPath(Promotion $promotion): string
    {
        return match ($promotion->case_use) {
            'take_away', 'delivery' => '/ordina',
            'table' => '/check-out',
            default => $this->safePromotionCtaPath($promotion->cta) ?: '/',
        };
    }

    private function ctaLabel(Promotion $promotion): string
    {
        return match ($promotion->case_use) {
            'take_away', 'delivery' => 'Ordina ora',
            'table' => 'Prenota ora',
            default => 'Scopri',
        };
    }

    private function safePromotionCtaPath(?string $cta): ?string
    {
        $cta = trim((string) $cta);

        if ($cta === '' || ! str_starts_with($cta, '/') || str_starts_with($cta, '//')) {
            return null;
        }

        if (preg_match('/[\r\n\t]/', $cta) === 1) {
            return null;
        }

        return $cta;
    }

    private function primaryTargetPayload(Promotion $promotion): array
    {
        $payload = [
            'target_type' => null,
            'target_name' => null,
            'product_name' => null,
            'menu_name' => null,
            'category_name' => null,
        ];

        $target = $promotion->targets
            ->first(fn (PromotionTarget $target) => ! $target->isGenericTarget());

        if (! $target) {
            return $payload;
        }

        $model = null;

        try {
            $model = $target->target();
        } catch (\Throwable) {
            $model = null;
        }

        $targetName = $model?->name ?? $model?->title ?? null;
        $payload['target_type'] = $target->target_type;
        $payload['target_name'] = $targetName;

        if ($target->isProductTarget()) {
            $payload['product_name'] = $targetName;
        } elseif ($target->isMenuTarget()) {
            $payload['menu_name'] = $targetName;
        } elseif ($target->isCategoryTarget()) {
            $payload['category_name'] = $targetName;
        }

        return $payload;
    }
}
