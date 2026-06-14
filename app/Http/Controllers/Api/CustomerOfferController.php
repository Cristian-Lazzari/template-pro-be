<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use App\Services\Marketing\CustomerPromotionService;
use App\Services\Marketing\PromotionAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class CustomerOfferController extends Controller
{
    public function publicIndex(Request $request)
    {
        $offers = [
            'available' => [],
            'used' => [],
            'expired' => [],
        ];

        if (! Schema::hasColumn('promotions', 'default_active')) {
            return response()->json([
                'success' => true,
                ...$offers,
            ]);
        }

        Promotion::query()
            ->with('targets')
            ->active()
            ->where('default_active', true)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->each(function (Promotion $promotion) use (&$offers) {
                if (! $this->isPublicPromotionAvailable($promotion)) {
                    return;
                }

                $offers['available'][] = $this->publicOfferPayload($promotion);
            });

        return response()->json([
            'success' => true,
            ...$offers,
        ]);
    }

    public function index(Request $request)
    {
        $customer = $request->user();

        if (! $customer instanceof Customer) {
            abort(403);
        }

        $this->assignDefaultPromotions($customer);

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

    private function publicOfferPayload(Promotion $promotion): array
    {
        $payload = $this->basePromotionPayload($promotion);
        $promotionId = $promotion->getKey();

        return array_merge($payload, [
            'id' => 'promotion-' . $promotionId,
            'customer_promotion_id' => null,
            'promotion_id' => $promotionId,
            'public_offer' => true,
            'status' => 'available',
            'assignment_status' => null,
            'expires_at' => $promotion->expiring_at?->toISOString(),
            'used_at' => null,
            'redeemed_at' => null,
        ]);
    }

    private function offerPayload(CustomerPromotion $customerPromotion): array
    {
        $promotion = $customerPromotion->promotion;
        $usedAt = $this->usedAt($customerPromotion);
        $customerPromotionExpiresAt = $this->customerPromotionExpiresAt($customerPromotion);
        $expiresAt = $customerPromotionExpiresAt ?: $promotion->expiring_at;
        $status = $this->offerStatus($customerPromotion, $promotion, $usedAt, $customerPromotionExpiresAt);
        $payload = $this->basePromotionPayload($promotion);

        return array_merge($payload, [
            'id' => $customerPromotion->getKey(),
            'customer_promotion_id' => $customerPromotion->getKey(),
            'promotion_id' => $promotion->getKey(),
            'public_offer' => false,
            'status' => $status,
            'assignment_status' => $customerPromotion->status,
            'expires_at' => $expiresAt?->toISOString(),
            'used_at' => $usedAt?->toISOString(),
            'redeemed_at' => $usedAt?->toISOString(),
        ]);
    }

    private function basePromotionPayload(Promotion $promotion): array
    {
        $targets = $this->targetsPayload($promotion);
        $target = $this->primaryTargetPayload($targets);
        $minimum = $promotion->minimum_pretest !== null ? (float) $promotion->minimum_pretest : null;
        $startsAt = $promotion->schedule_at;
        $expiringAt = $promotion->expiring_at;
        $availability = app(PromotionAvailabilityService::class)->payload($promotion);

        return [
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
            'starts_at' => $startsAt?->toISOString(),
            'schedule_at' => $startsAt?->toISOString(),
            'expiring_at' => $expiringAt?->toISOString(),
            'permanent' => (bool) $promotion->permanent,
            'default_active' => (bool) $promotion->default_active,
            'cta_path' => $this->ctaPath($promotion),
            'cta_label' => $this->ctaLabel($promotion),
            'image' => $target['target_image'],
            'targets' => $targets,
            'target_type' => $target['target_type'],
            'target_id' => $target['target_id'],
            'target_name' => $target['target_name'],
            'target_image' => $target['target_image'],
            'product_name' => $target['product_name'],
            'product_image' => $target['product_image'],
            'menu_name' => $target['menu_name'],
            'menu_image' => $target['menu_image'],
            'category_name' => $target['category_name'],
            'category_image' => $target['category_image'],
        ];
    }

    private function isPublicPromotionAvailable(Promotion $promotion): bool
    {
        if (! $promotion->isActive()) {
            return false;
        }

        if ($promotion->schedule_at?->isFuture()) {
            return false;
        }

        if (! $promotion->isPermanent() && $promotion->expiring_at?->isPast()) {
            return false;
        }

        return true;
    }

    private function assignDefaultPromotions(Customer $customer): void
    {
        if (! Schema::hasColumn('promotions', 'default_active')) {
            return;
        }

        $service = app(CustomerPromotionService::class);

        Promotion::query()
            ->active()
            ->where('default_active', true)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->each(function (Promotion $promotion) use ($customer, $service) {
                if ($this->hasOpenCustomerPromotion($customer, $promotion)) {
                    return;
                }

                try {
                    $service->assignToCustomer($customer, $promotion, null, null, [
                        'source' => 'default_active_promotion',
                        'default_assigned_at' => now()->toISOString(),
                    ]);
                } catch (InvalidArgumentException) {
                    // Expired, scheduled, or already-used non-reusable promotions stay hidden.
                }
            });
    }

    private function hasOpenCustomerPromotion(Customer $customer, Promotion $promotion): bool
    {
        return $customer->customerPromotions()
            ->where('promotion_id', $promotion->getKey())
            ->whereNull('promo_used')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'used');
            })
            ->exists();
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
            'generic', 'take_away', 'delivery' => '/ordina',
            'table' => '/check-out',
            default => '/',
        };
    }

    private function ctaLabel(Promotion $promotion): string
    {
        $configuredLabel = trim((string) $promotion->cta);

        if ($configuredLabel !== '' && ! $this->looksLikeLink($configuredLabel)) {
            return $configuredLabel;
        }

        return match ($promotion->case_use) {
            'generic', 'take_away', 'delivery' => 'Ordina ora',
            'table' => 'Prenota ora',
            default => 'Scopri',
        };
    }

    private function looksLikeLink(string $value): bool
    {
        return str_starts_with($value, '/')
            || preg_match('#^[a-z][a-z0-9+.-]*://#i', $value) === 1
            || str_contains($value, '\\');
    }

    private function primaryTargetPayload(array $targets): array
    {
        $payload = [
            'target_type' => null,
            'target_id' => null,
            'target_name' => null,
            'target_image' => null,
            'product_name' => null,
            'product_image' => null,
            'menu_name' => null,
            'menu_image' => null,
            'category_name' => null,
            'category_image' => null,
        ];

        $target = collect($targets)->first(fn (array $target) => $target['type'] !== PromotionTarget::TYPE_GENERIC)
            ?: ($targets[0] ?? null);

        if (! $target) {
            return $payload;
        }

        $payload['target_type'] = $target['type'];
        $payload['target_id'] = $target['id'];
        $payload['target_name'] = $target['name'];
        $payload['target_image'] = $target['image'] ?? null;

        if ($target['type'] === PromotionTarget::TYPE_PRODUCT) {
            $payload['product_name'] = $target['name'];
            $payload['product_image'] = $target['image'] ?? null;
        } elseif ($target['type'] === PromotionTarget::TYPE_MENU) {
            $payload['menu_name'] = $target['name'];
            $payload['menu_image'] = $target['image'] ?? null;
        } elseif ($target['type'] === PromotionTarget::TYPE_CATEGORY) {
            $payload['category_name'] = $target['name'];
            $payload['category_image'] = $target['image'] ?? null;
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
                'image' => null,
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
            'image' => $this->targetImage($target),
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

    private function targetImage(PromotionTarget $target): ?string
    {
        $config = match ($target->target_type) {
            PromotionTarget::TYPE_PRODUCT => [
                'table' => 'products',
                'image_column' => 'image',
            ],
            PromotionTarget::TYPE_MENU => [
                'table' => 'menus',
                'image_column' => 'image',
            ],
            PromotionTarget::TYPE_CATEGORY => [
                'table' => 'categories',
                'image_column' => 'icon',
            ],
            default => null,
        };

        if (! $config || ! Schema::hasTable($config['table']) || ! Schema::hasColumn($config['table'], $config['image_column'])) {
            return null;
        }

        $image = DB::table($config['table'])
            ->where('id', $target->target_id)
            ->value($config['image_column']);

        return is_string($image) && trim($image) !== '' ? trim($image) : null;
    }
}
