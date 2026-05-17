<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use App\Services\Marketing\PromotionAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $customerPromotionExpiresAt = $this->customerPromotionExpiresAt($customerPromotion);
        $expiresAt = $customerPromotionExpiresAt ?: $promotion->expiring_at;
        $status = $this->offerStatus($customerPromotion, $promotion, $usedAt, $customerPromotionExpiresAt);
        $targets = $this->targetsPayload($promotion);
        $target = $this->primaryTargetPayload($targets);
        $minimum = $promotion->minimum_pretest !== null ? (float) $promotion->minimum_pretest : null;
        $startsAt = $promotion->schedule_at;
        $expiringAt = $promotion->expiring_at;
        $availability = app(PromotionAvailabilityService::class)->payload($promotion);

        return [
            'id' => $customerPromotion->getKey(),
            'customer_promotion_id' => $customerPromotion->getKey(),
            'promotion_id' => $promotion->getKey(),
            'name' => $promotion->name,
            'title' => $promotion->name,
            'description' => $this->promotionDescription($promotion),
            'case_use' => $promotion->case_use ?: 'generic',
            'discount_label' => $this->discountLabel($promotion),
            'type_discount' => $promotion->type_discount,
            'discount' => $promotion->discount !== null ? (float) $promotion->discount : null,
            'minimum_pretest' => $minimum,
            'minimum_required' => $minimum,
            'valid_weekdays' => $availability['valid_weekdays'],
            'valid_from_time' => $availability['valid_from_time'],
            'valid_to_time' => $availability['valid_to_time'],
            'availability' => $availability,
            'is_available_now' => $availability['is_available_now'],
            'availability_unavailable_reason' => $availability['unavailable_reason'],
            'status' => $status,
            'assignment_status' => $customerPromotion->status,
            'starts_at' => $startsAt?->toISOString(),
            'schedule_at' => $startsAt?->toISOString(),
            'expires_at' => $expiresAt?->toISOString(),
            'expiring_at' => $expiringAt?->toISOString(),
            'permanent' => (bool) $promotion->permanent,
            'used_at' => $usedAt?->toISOString(),
            'redeemed_at' => $usedAt?->toISOString(),
            'cta_path' => $this->ctaPath($promotion),
            'cta_label' => $this->ctaLabel($promotion),
            'targets' => $targets,
            'target_type' => $target['target_type'],
            'target_id' => $target['target_id'],
            'target_name' => $target['target_name'],
            'product_name' => $target['product_name'],
            'menu_name' => $target['menu_name'],
            'category_name' => $target['category_name'],
        ];
    }

    private function offerStatus(
        CustomerPromotion $customerPromotion,
        Promotion $promotion,
        ?Carbon $usedAt,
        ?Carbon $customerPromotionExpiresAt
    ): string
    {
        if ($usedAt || $customerPromotion->status === 'used') {
            return 'used';
        }

        if (! $promotion->isActive()) {
            return 'expired';
        }

        if ($promotion->schedule_at?->isFuture()) {
            return 'expired';
        }

        if ($customerPromotionExpiresAt?->isPast()) {
            return 'expired';
        }

        if (! $promotion->isPermanent() && $promotion->expiring_at?->isPast()) {
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

    private function primaryTargetPayload(array $targets): array
    {
        $payload = [
            'target_type' => null,
            'target_id' => null,
            'target_name' => null,
            'product_name' => null,
            'menu_name' => null,
            'category_name' => null,
        ];

        $target = collect($targets)->first(fn (array $target) => $target['type'] !== PromotionTarget::TYPE_GENERIC)
            ?: ($targets[0] ?? null);

        if (! $target) {
            return $payload;
        }

        $payload['target_type'] = $target['type'];
        $payload['target_id'] = $target['id'];
        $payload['target_name'] = $target['name'];

        if ($target['type'] === PromotionTarget::TYPE_PRODUCT) {
            $payload['product_name'] = $target['name'];
        } elseif ($target['type'] === PromotionTarget::TYPE_MENU) {
            $payload['menu_name'] = $target['name'];
        } elseif ($target['type'] === PromotionTarget::TYPE_CATEGORY) {
            $payload['category_name'] = $target['name'];
        }

        return $payload;
    }

    private function targetsPayload(Promotion $promotion): array
    {
        return $promotion->targets
            ->map(fn (PromotionTarget $target) => $this->targetPayload($target))
            ->filter()
            ->values()
            ->all();
    }

    private function targetPayload(PromotionTarget $target): ?array
    {
        if ($target->isGenericTarget()) {
            return [
                'type' => PromotionTarget::TYPE_GENERIC,
                'id' => null,
                'name' => 'Tutto l\'ordine',
            ];
        }

        if (! in_array($target->target_type, [
            PromotionTarget::TYPE_PRODUCT,
            PromotionTarget::TYPE_MENU,
            PromotionTarget::TYPE_CATEGORY,
        ], true)) {
            return null;
        }

        if (! $target->target_id) {
            return null;
        }

        $name = $this->targetName($target);

        if ($name === null) {
            return null;
        }

        return [
            'type' => $target->target_type,
            'id' => (int) $target->target_id,
            'name' => $name,
        ];
    }

    private function targetName(PromotionTarget $target): ?string
    {
        $config = match ($target->target_type) {
            PromotionTarget::TYPE_PRODUCT => [
                'table' => 'products',
                'translation_table' => 'product_translations',
                'foreign_key' => 'product_id',
            ],
            PromotionTarget::TYPE_MENU => [
                'table' => 'menus',
                'translation_table' => 'menu_translations',
                'foreign_key' => 'menu_id',
            ],
            PromotionTarget::TYPE_CATEGORY => [
                'table' => 'categories',
                'translation_table' => 'category_translations',
                'foreign_key' => 'category_id',
            ],
            default => null,
        };

        if (! $config || ! Schema::hasTable($config['table'])) {
            return null;
        }

        $exists = DB::table($config['table'])->where('id', $target->target_id)->exists();

        if (! $exists) {
            return null;
        }

        if (Schema::hasTable($config['translation_table'])) {
            $name = DB::table($config['translation_table'])
                ->where($config['foreign_key'], $target->target_id)
                ->where('lang', app()->getLocale())
                ->value('name')
                ?: DB::table($config['translation_table'])
                    ->where($config['foreign_key'], $target->target_id)
                    ->where('lang', config('app.fallback_locale', 'en'))
                    ->value('name')
                ?: DB::table($config['translation_table'])
                    ->where($config['foreign_key'], $target->target_id)
                    ->value('name');

            if (is_string($name) && trim($name) !== '') {
                return trim($name);
            }
        }

        if (Schema::hasColumn($config['table'], 'name')) {
            $name = DB::table($config['table'])->where('id', $target->target_id)->value('name');

            if (is_string($name) && trim($name) !== '') {
                return trim($name);
            }
        }

        return null;
    }
}
