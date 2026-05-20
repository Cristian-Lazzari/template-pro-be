<?php

namespace App\Services\Marketing;

use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use App\Support\Currency;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderPromotionApplicationService
{
    private const ORDER_CASES = ['generic', 'take_away', 'delivery'];

    public function __construct(
        private PromotionAvailabilityService $promotionAvailabilityService,
    ) {
    }

    public function evaluate(Customer $customer, array $cart, ?int $customerPromotionId = null): array
    {
        $cartSnapshot = $this->buildCartSnapshot($cart);
        $validAt = $this->promotionDateTimeFromCart($cart);

        if ($cartSnapshot['error'] !== null) {
            return $this->result(
                false,
                $cartSnapshot['error'],
                null,
                null,
                0,
                $cartSnapshot['subtotal'],
                [],
                null
            );
        }

        if ($customerPromotionId === null) {
            return $this->findBestApplicableFromSnapshot($customer, $cart, $cartSnapshot, $validAt);
        }

        $customerPromotion = CustomerPromotion::query()
            ->with(['promotion.targets'])
            ->find($customerPromotionId);

        return $this->evaluateCustomerPromotion($customer, $cart, $cartSnapshot, $customerPromotion, $validAt);
    }

    public function findBestApplicable(Customer $customer, array $cart): array
    {
        $cartSnapshot = $this->buildCartSnapshot($cart);

        if ($cartSnapshot['error'] !== null) {
            return $this->result(
                false,
                $cartSnapshot['error'],
                null,
                null,
                0,
                $cartSnapshot['subtotal'],
                [],
                null
            );
        }

        return $this->findBestApplicableFromSnapshot($customer, $cart, $cartSnapshot, $this->promotionDateTimeFromCart($cart));
    }

    private function findBestApplicableFromSnapshot(Customer $customer, array $cart, array $cartSnapshot, Carbon $validAt): array
    {
        $results = CustomerPromotion::query()
            ->with(['promotion.targets'])
            ->where('customer_id', $customer->getKey())
            ->whereNull('promo_used')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'used');
            })
            ->get()
            ->map(fn (CustomerPromotion $customerPromotion) => $this->evaluateCustomerPromotion(
                $customer,
                $cart,
                $cartSnapshot,
                $customerPromotion,
                $validAt
            ))
            ->filter(fn (array $result) => $result['applicable'])
            ->sortByDesc(fn (array $result) => $result['discount_amount'])
            ->values();

        if ($results->isEmpty()) {
            return $this->result(
                false,
                'no_applicable_promotion',
                null,
                null,
                0,
                $cartSnapshot['subtotal'],
                [],
                null
            );
        }

        return $results->first();
    }

    private function evaluateCustomerPromotion(
        Customer $customer,
        array $cart,
        array $cartSnapshot,
        ?CustomerPromotion $customerPromotion,
        Carbon $validAt
    ): array {
        $baseSubtotal = $cartSnapshot['subtotal'];

        if (! $customerPromotion) {
            return $this->result(false, 'customer_promotion_not_found', null, null, 0, $baseSubtotal, [], null);
        }

        if ((int) $customerPromotion->customer_id !== (int) $customer->getKey()) {
            return $this->result(false, 'customer_promotion_customer_mismatch', null, null, 0, $baseSubtotal, [], null);
        }

        $promotion = $customerPromotion->promotion;

        if (! $promotion) {
            return $this->result(false, 'promotion_not_found', $customerPromotion, null, 0, $baseSubtotal, [], null);
        }

        $failureReason = $this->validateAvailability($customer, $customerPromotion, $promotion, $cart, $validAt);

        if ($failureReason !== null) {
            return $this->result(
                false,
                $failureReason['reason'],
                $customerPromotion,
                $promotion,
                0,
                $baseSubtotal,
                [],
                $failureReason['minimum_required'] ?? null
            );
        }

        $application = $this->calculateDiscount($promotion, $cartSnapshot['items']);

        if (! $application['applicable']) {
            return $this->result(
                false,
                $application['reason'],
                $customerPromotion,
                $promotion,
                0,
                $baseSubtotal,
                [],
                null
            );
        }

        $minimumFailure = $this->validateDiscountedMinimum($promotion, $baseSubtotal, $application['discount_amount']);

        if ($minimumFailure !== null) {
            return $this->result(
                false,
                $minimumFailure['reason'],
                $customerPromotion,
                $promotion,
                0,
                $baseSubtotal,
                [],
                $minimumFailure['minimum_required'],
                $minimumFailure['minimum_checked_total']
            );
        }

        return $this->result(
            true,
            null,
            $customerPromotion,
            $promotion,
            $application['discount_amount'],
            $baseSubtotal,
            $application['affected_items'],
            null
        );
    }

    private function validateAvailability(
        Customer $customer,
        CustomerPromotion $customerPromotion,
        Promotion $promotion,
        array $cart,
        Carbon $validAt
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

        if (! $promotion->isPermanent()) {
            if ($promotion->schedule_at && $promotion->schedule_at->isFuture()) {
                return ['reason' => 'promotion_not_started'];
            }

            if ($promotion->expiring_at && $promotion->expiring_at->isPast()) {
                return ['reason' => 'promotion_expired'];
            }
        }

        if (! $this->isOrderCaseUseCompatible($promotion, $cart)) {
            return ['reason' => 'invalid_case_use'];
        }

        $availabilityReason = $this->promotionAvailabilityService->unavailableReason($promotion, $validAt);

        if ($availabilityReason !== null) {
            return ['reason' => $availabilityReason];
        }

        if (! $promotion->isReusable() && $this->customerAlreadyUsedPromotion($promotion, $customer)) {
            return ['reason' => 'promotion_already_used_by_customer'];
        }

        return null;
    }

    private function validateDiscountedMinimum(Promotion $promotion, float $subtotal, float $discountAmount): ?array
    {
        $minimumRequired = $promotion->minimum_pretest !== null
            ? Currency::roundAmount($promotion->minimum_pretest)
            : null;

        if ($minimumRequired === null) {
            return null;
        }

        $discountedTotal = Currency::roundAmount(max(0, $subtotal - $discountAmount));

        if ($discountedTotal >= $minimumRequired) {
            return null;
        }

        return [
            'reason' => 'minimum_not_reached',
            'minimum_required' => $minimumRequired,
            'minimum_checked_total' => $discountedTotal,
        ];
    }

    private function calculateDiscount(Promotion $promotion, array $items): array
    {
        $discountType = $this->normalize((string) $promotion->type_discount);
        $targets = $promotion->relationLoaded('targets')
            ? $promotion->targets
            : $promotion->targets()->get();

        if ($discountType === 'gift') {
            return $this->calculateGiftDiscount($promotion, $targets, $items);
        }

        $affectedItems = $this->matchingItems($targets, $items);

        if ($affectedItems->isEmpty()) {
            return [
                'applicable' => false,
                'reason' => 'no_matching_items',
                'discount_amount' => 0.0,
                'affected_items' => [],
            ];
        }

        $discountItem = $this->singleDiscountableItem($affectedItems);

        if (! $discountItem) {
            return [
                'applicable' => false,
                'reason' => 'no_matching_items',
                'discount_amount' => 0.0,
                'affected_items' => [],
            ];
        }

        $affectedTotal = Currency::roundAmount(min($discountItem['unit_price'], $discountItem['line_total']));
        $discountValue = Currency::roundAmount($promotion->discount);

        if ($discountType === 'fixed') {
            $discountAmount = Currency::roundAmount(min($discountValue, $affectedTotal));
        } elseif ($discountType === 'percentage') {
            $discountAmount = Currency::roundAmount(min($affectedTotal, $affectedTotal * ($discountValue / 100)));
        } else {
            return [
                'applicable' => false,
                'reason' => 'unsupported_discount_type',
                'discount_amount' => 0.0,
                'affected_items' => [],
            ];
        }

        if ($discountAmount <= 0) {
            return [
                'applicable' => false,
                'reason' => 'invalid_discount_config',
                'discount_amount' => 0.0,
                'affected_items' => [],
            ];
        }

        return [
            'applicable' => true,
            'reason' => null,
            'discount_amount' => $discountAmount,
            'affected_items' => [
                $this->affectedSingleUnitPayload($discountItem, $discountAmount, $promotion),
            ],
        ];
    }

    private function calculateGiftDiscount(Promotion $promotion, Collection $targets, array $items): array
    {
        if ($targets->isEmpty() || $targets->contains(fn (PromotionTarget $target) => $target->isGenericTarget())) {
            return [
                'applicable' => false,
                'reason' => 'unsupported_gift_target',
                'discount_amount' => 0.0,
                'affected_items' => [],
            ];
        }

        $giftTargets = $targets->filter(
            fn (PromotionTarget $target) => $target->isProductTarget()
                || $target->isMenuTarget()
                || $target->isCategoryTarget()
        );

        if ($giftTargets->isEmpty()) {
            return [
                'applicable' => false,
                'reason' => 'unsupported_gift_target',
                'discount_amount' => 0.0,
                'affected_items' => [],
            ];
        }

        $affectedItems = $this->matchingItems($giftTargets, $items);

        if ($affectedItems->isEmpty()) {
            return [
                'applicable' => false,
                'reason' => 'no_matching_items',
                'discount_amount' => 0.0,
                'affected_items' => [],
            ];
        }

        $giftItem = $this->singleDiscountableItem($affectedItems);

        if (! $giftItem) {
            return [
                'applicable' => false,
                'reason' => 'no_matching_items',
                'discount_amount' => 0.0,
                'affected_items' => [],
            ];
        }

        $discountAmount = Currency::roundAmount($giftItem['unit_price']);

        if ($discountAmount <= 0) {
            return [
                'applicable' => false,
                'reason' => 'invalid_discount_config',
                'discount_amount' => 0.0,
                'affected_items' => [],
            ];
        }

        return [
            'applicable' => true,
            'reason' => null,
            'discount_amount' => $discountAmount,
            'affected_items' => [
                $this->affectedSingleUnitPayload($giftItem, $discountAmount, $promotion, true),
            ],
        ];
    }

    private function singleDiscountableItem(Collection $items): ?array
    {
        return $items
            ->filter(fn (array $item) => ($item['unit_price'] ?? 0) > 0 && ($item['line_total'] ?? 0) > 0)
            ->sort(function (array $left, array $right) {
                return [
                    Currency::roundAmount($right['unit_price'] ?? 0),
                    Currency::roundAmount($right['line_total'] ?? 0),
                ] <=> [
                    Currency::roundAmount($left['unit_price'] ?? 0),
                    Currency::roundAmount($left['line_total'] ?? 0),
                ];
            })
            ->first();
    }

    private function matchingItems(Collection $targets, array $items): Collection
    {
        if ($targets->contains(fn (PromotionTarget $target) => $target->isGenericTarget())) {
            return collect($items)->values();
        }

        $productIds = $targets
            ->where('target_type', PromotionTarget::TYPE_PRODUCT)
            ->pluck('target_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        $menuIds = $targets
            ->where('target_type', PromotionTarget::TYPE_MENU)
            ->pluck('target_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        $categoryIds = $targets
            ->where('target_type', PromotionTarget::TYPE_CATEGORY)
            ->pluck('target_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->all();

        return collect($items)
            ->filter(function (array $item) use ($productIds, $menuIds, $categoryIds) {
                return ($item['type'] === PromotionTarget::TYPE_PRODUCT && in_array($item['id'], $productIds, true))
                    || ($item['type'] === PromotionTarget::TYPE_MENU && in_array($item['id'], $menuIds, true))
                    || ($item['type'] === PromotionTarget::TYPE_PRODUCT && in_array($item['category_id'], $categoryIds, true));
            })
            ->values();
    }

    private function buildCartSnapshot(array $cart): array
    {
        $items = [];
        $subtotal = 0.0;

        foreach (($cart['products'] ?? []) as $index => $cartProduct) {
            $productId = (int) ($cartProduct['id'] ?? 0);
            $product = $this->productSnapshot($productId);

            if (! $product) {
                return $this->cartSnapshot($items, $subtotal, 'invalid_cart_item');
            }

            $quantity = $this->cartQuantity($cartProduct['counter'] ?? 1);
            $modifierUnitPrice = $this->ingredientModifierTotal(array_merge(
                $this->arrayValue($cartProduct['add'] ?? []),
                $this->arrayValue($cartProduct['option'] ?? [])
            ));

            if ($modifierUnitPrice === null) {
                return $this->cartSnapshot($items, $subtotal, 'invalid_cart_item');
            }

            $unitPrice = Currency::roundAmount($product->price + $modifierUnitPrice);
            $lineTotal = Currency::roundAmount($unitPrice * $quantity);
            $subtotal = Currency::roundAmount($subtotal + $lineTotal);

            $items[] = [
                'type' => PromotionTarget::TYPE_PRODUCT,
                'id' => (int) $product->id,
                'name' => $this->snapshotName($product),
                'category_id' => (int) $product->category_id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'base_unit_price' => Currency::roundAmount($product->price),
                'modifier_unit_price' => Currency::roundAmount($modifierUnitPrice),
                'cart_index' => $index,
            ];
        }

        foreach (($cart['menus'] ?? []) as $index => $cartMenu) {
            $menuId = (int) ($cartMenu['id'] ?? 0);
            $menu = $this->menuSnapshot($menuId);

            if (! $menu) {
                return $this->cartSnapshot($items, $subtotal, 'invalid_cart_item');
            }

            $quantity = $this->cartQuantity($cartMenu['counter'] ?? 1);
            $extraUnitPrice = $this->menuExtraTotal($menu, $cartMenu);
            $unitPrice = Currency::roundAmount($menu->price + $extraUnitPrice);
            $lineTotal = Currency::roundAmount($unitPrice * $quantity);
            $subtotal = Currency::roundAmount($subtotal + $lineTotal);

            $items[] = [
                'type' => PromotionTarget::TYPE_MENU,
                'id' => (int) $menu->id,
                'name' => $this->snapshotName($menu),
                'category_id' => (int) $menu->category_id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'base_unit_price' => Currency::roundAmount($menu->price),
                'extra_unit_price' => Currency::roundAmount($extraUnitPrice),
                'cart_index' => $index,
            ];
        }

        return $this->cartSnapshot($items, $subtotal, null);
    }

    private function productSnapshot(int $productId): ?object
    {
        if ($productId <= 0) {
            return null;
        }

        $columns = ['id', 'category_id', 'price'];

        if (Schema::hasColumn('products', 'name')) {
            $columns[] = 'name';
        }

        $product = DB::table('products')
            ->select($columns)
            ->where('id', $productId)
            ->first();

        if ($product && ! isset($product->name)) {
            $product->name = $this->translatedName('product_translations', 'product_id', $productId);
        }

        return $product;
    }

    private function menuSnapshot(int $menuId): ?object
    {
        if ($menuId <= 0) {
            return null;
        }

        $columns = ['id', 'category_id', 'price', 'fixed_menu'];

        if (Schema::hasColumn('menus', 'name')) {
            $columns[] = 'name';
        }

        $menu = DB::table('menus')
            ->select($columns)
            ->where('id', $menuId)
            ->first();

        if ($menu && ! isset($menu->name)) {
            $menu->name = $this->translatedName('menu_translations', 'menu_id', $menuId);
        }

        return $menu;
    }

    private function translatedName(string $table, string $foreignKey, int $id): ?string
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $query = DB::table($table)->where($foreignKey, $id);

        if (Schema::hasColumn($table, 'lang')) {
            $query->orderByRaw('CASE WHEN lang = ? THEN 0 ELSE 1 END', [app()->getLocale()]);
        }

        $name = $query->value('name');

        return filled($name) ? (string) $name : null;
    }

    private function snapshotName(object $snapshot): ?string
    {
        return isset($snapshot->name) && filled($snapshot->name)
            ? (string) $snapshot->name
            : null;
    }

    private function ingredientModifierTotal(array $names): ?float
    {
        $total = 0.0;

        foreach ($names as $name) {
            $name = trim((string) $name);

            if ($name === '') {
                continue;
            }

            $ingredient = $this->ingredientByName($name);

            if (! $ingredient) {
                return null;
            }

            $total = Currency::roundAmount($total + $ingredient->price);
        }

        return $total;
    }

    private function ingredientByName(string $name): ?object
    {
        if (Schema::hasColumn('ingredients', 'name')) {
            $ingredient = DB::table('ingredients')
                ->select(['id', 'price'])
                ->where('name', $name)
                ->first();

            if ($ingredient) {
                return $ingredient;
            }
        }

        if (! Schema::hasTable('ingredient_translations')) {
            return null;
        }

        return DB::table('ingredients')
            ->join('ingredient_translations', 'ingredient_translations.ingredient_id', '=', 'ingredients.id')
            ->select(['ingredients.id', 'ingredients.price'])
            ->where('ingredient_translations.name', $name)
            ->first();
    }

    private function menuExtraTotal(object $menu, array $cartMenu): float
    {
        if ((string) $menu->fixed_menu !== '2') {
            return 0.0;
        }

        $selectedProductIds = collect($cartMenu['products'] ?? [])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedProductIds->isEmpty()) {
            return 0.0;
        }

        return Currency::roundAmount(DB::table('menu_product')
            ->where('menu_id', $menu->id)
            ->whereIn('product_id', $selectedProductIds->all())
            ->sum('extra_price'));
    }

    private function affectedItemPayloads(Collection $items, float $discountAmount, float $affectedTotal, Promotion $promotion): array
    {
        $remainingDiscount = $discountAmount;
        $lastIndex = $items->count() - 1;

        return $items
            ->values()
            ->map(function (array $item, int $index) use (&$remainingDiscount, $discountAmount, $affectedTotal, $lastIndex, $promotion) {
                $itemDiscount = $index === $lastIndex
                    ? $remainingDiscount
                    : Currency::roundAmount($discountAmount * ($item['line_total'] / $affectedTotal));

                $itemDiscount = Currency::roundAmount(min($itemDiscount, $item['line_total'], $remainingDiscount));
                $remainingDiscount = Currency::roundAmount($remainingDiscount - $itemDiscount);

                return array_merge($this->affectedItemPayload($item, $promotion), [
                    'discount_amount' => $itemDiscount,
                ]);
            })
            ->all();
    }

    private function affectedSingleUnitPayload(array $item, float $discountAmount, Promotion $promotion, bool $isGift = false): array
    {
        $applicableAmount = Currency::roundAmount(min($item['unit_price'], $item['line_total']));
        $discountAmount = Currency::roundAmount(min($discountAmount, $applicableAmount));

        $payload = array_merge($this->affectedItemPayload($item, $promotion), [
            'applicable_amount' => $applicableAmount,
            'discounted_quantity' => $discountAmount > 0 ? 1 : 0,
            'discount_amount' => $discountAmount,
            'preview_total' => Currency::roundAmount(max(0, $item['line_total'] - $discountAmount)),
        ]);

        if ($isGift) {
            $payload['gift_quantity'] = $discountAmount > 0 ? 1 : 0;
            $payload['is_gift'] = true;
        }

        return $payload;
    }

    private function affectedItemPayload(array $item, ?Promotion $promotion = null): array
    {
        $payload = [
            'type' => $item['type'],
            'id' => $item['id'],
            'name' => $item['name'] ?? null,
            'category_id' => $item['category_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'line_total' => $item['line_total'],
            'applicable_amount' => $item['line_total'],
            'cart_index' => $item['cart_index'] ?? null,
        ];

        if ($promotion) {
            $payload['discount_label'] = $this->promotionDiscountLabel($promotion);
            $payload['promotion_name'] = $promotion->name;
            $payload['type_discount'] = $promotion->type_discount;
        }

        return $payload;
    }

    private function promotionDiscountLabel(Promotion $promotion): ?string
    {
        $type = $this->normalize((string) $promotion->type_discount);

        if ($type === 'gift') {
            return 'Omaggio';
        }

        if ($type === 'percentage' && $promotion->discount !== null) {
            return '-' . rtrim(rtrim(number_format((float) $promotion->discount, 2, ',', ''), '0'), ',') . '%';
        }

        if ($type === 'fixed' && $promotion->discount !== null) {
            return '-' . Currency::formatCents($promotion->discount);
        }

        return null;
    }

    private function result(
        bool $applicable,
        ?string $reason,
        ?CustomerPromotion $customerPromotion,
        ?Promotion $promotion,
        float $discountAmount,
        float $subtotal,
        array $affectedItems,
        ?float $minimumRequired,
        ?float $minimumCheckedTotal = null
    ): array {
        $discountAmount = Currency::roundAmount(min($discountAmount, $subtotal));
        $subtotal = Currency::roundAmount($subtotal);
        $minimumMissingBase = $minimumCheckedTotal ?? $subtotal;
        $minimumMissing = $minimumRequired !== null
            ? Currency::roundAmount(max(0, $minimumRequired - $minimumMissingBase))
            : 0.0;

        return [
            'applicable' => $applicable,
            'reason' => $reason,
            'customer_promotion' => $customerPromotion,
            'promotion' => $promotion,
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal,
            'total' => Currency::roundAmount(max(0, $subtotal - $discountAmount)),
            'affected_items' => $affectedItems,
            'minimum_required' => $minimumRequired,
            'minimum_missing' => $minimumMissing,
            'minimum_checked_total' => $minimumCheckedTotal !== null
                ? Currency::roundAmount($minimumCheckedTotal)
                : null,
        ];
    }

    private function cartSnapshot(array $items, float $subtotal, ?string $error): array
    {
        return [
            'items' => $items,
            'subtotal' => Currency::roundAmount($subtotal),
            'error' => $error,
        ];
    }

    private function cartQuantity($value): int
    {
        $quantity = (int) $value;

        return $quantity > 0 ? $quantity : 1;
    }

    private function arrayValue($value): array
    {
        return is_array($value) ? $value : [];
    }

    private function isOrderCaseUseCompatible(Promotion $promotion, array $cart): bool
    {
        $promotionCaseUse = $this->normalize((string) ($promotion->case_use ?: 'generic'));

        if (! in_array($promotionCaseUse, self::ORDER_CASES, true)) {
            return false;
        }

        if ($promotionCaseUse === 'generic') {
            return true;
        }

        $cartCaseUse = $this->cartCaseUse($cart);

        return $cartCaseUse === null || $cartCaseUse === 'generic' || $cartCaseUse === $promotionCaseUse;
    }

    private function cartCaseUse(array $cart): ?string
    {
        foreach (['case_use', 'order_case_use', 'service_type', 'fulfillment_type', 'order_type', 'type'] as $key) {
            $value = $cart[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return $this->normalizeOrderCaseUse($value);
            }
        }

        return null;
    }

    private function promotionDateTimeFromCart(array $cart): Carbon
    {
        foreach (['date_slot', 'scheduled_at', 'pickup_at', 'delivery_at'] as $key) {
            if (! empty($cart[$key])) {
                return $this->promotionAvailabilityService->resolveDateTime($cart[$key]);
            }
        }

        return $this->promotionAvailabilityService->resolveDateTime(null);
    }

    private function normalizeOrderCaseUse(string $caseUse): string
    {
        $caseUse = $this->normalize($caseUse);

        return match ($caseUse) {
            'asporto', 'takeaway', 'take-away', 'take_away', 'pickup', 'ritiro' => 'take_away',
            'domicilio', 'delivery', 'consegna' => 'delivery',
            default => $caseUse,
        };
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function customerAlreadyUsedPromotion(Promotion $promotion, Customer $customer): bool
    {
        if ($promotion->isReusable()) {
            return false;
        }

        return CustomerPromotion::query()
            ->where('customer_id', $customer->getKey())
            ->where('promotion_id', $promotion->getKey())
            ->where(function ($query) {
                $query->whereNotNull('promo_used')
                    ->orWhere('status', 'used');
            })
            ->exists();
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
}
