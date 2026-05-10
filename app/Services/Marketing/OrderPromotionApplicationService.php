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

    public function evaluate(Customer $customer, array $cart, ?int $customerPromotionId = null): array
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

        if ($customerPromotionId === null) {
            return $this->findBestApplicableFromSnapshot($customer, $cart, $cartSnapshot);
        }

        $customerPromotion = CustomerPromotion::query()
            ->with(['promotion.targets'])
            ->find($customerPromotionId);

        return $this->evaluateCustomerPromotion($customer, $cart, $cartSnapshot, $customerPromotion);
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

        return $this->findBestApplicableFromSnapshot($customer, $cart, $cartSnapshot);
    }

    private function findBestApplicableFromSnapshot(Customer $customer, array $cart, array $cartSnapshot): array
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
                $customerPromotion
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
        ?CustomerPromotion $customerPromotion
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

        $failureReason = $this->validateAvailability($customer, $customerPromotion, $promotion, $baseSubtotal, $cart);

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
        float $subtotal,
        array $cart
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

        $minimumRequired = $promotion->minimum_pretest !== null
            ? Currency::roundAmount($promotion->minimum_pretest)
            : null;

        if ($minimumRequired !== null && $subtotal < $minimumRequired) {
            return [
                'reason' => 'minimum_not_reached',
                'minimum_required' => $minimumRequired,
            ];
        }

        if (! $promotion->isReusable() && $this->customerAlreadyUsedPromotion($promotion, $customer)) {
            return ['reason' => 'promotion_already_used_by_customer'];
        }

        return null;
    }

    private function calculateDiscount(Promotion $promotion, array $items): array
    {
        $discountType = $this->normalize((string) $promotion->type_discount);
        $targets = $promotion->relationLoaded('targets')
            ? $promotion->targets
            : $promotion->targets()->get();

        if ($discountType === 'gift') {
            return $this->calculateGiftDiscount($targets, $items);
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

        $affectedTotal = Currency::roundAmount($affectedItems->sum('line_total'));
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
            'affected_items' => $this->affectedItemPayloads($affectedItems, $discountAmount, $affectedTotal),
        ];
    }

    private function calculateGiftDiscount(Collection $targets, array $items): array
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
            fn (PromotionTarget $target) => $target->isProductTarget() || $target->isMenuTarget()
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

        $giftItem = $affectedItems
            ->sortByDesc('unit_price')
            ->first();

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
                array_merge($this->affectedItemPayload($giftItem), [
                    'discount_amount' => $discountAmount,
                    'gift_quantity' => 1,
                ]),
            ],
        ];
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

        return DB::table('products')
            ->select(['id', 'category_id', 'price'])
            ->where('id', $productId)
            ->first();
    }

    private function menuSnapshot(int $menuId): ?object
    {
        if ($menuId <= 0) {
            return null;
        }

        return DB::table('menus')
            ->select(['id', 'category_id', 'price', 'fixed_menu'])
            ->where('id', $menuId)
            ->first();
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

    private function affectedItemPayloads(Collection $items, float $discountAmount, float $affectedTotal): array
    {
        $remainingDiscount = $discountAmount;
        $lastIndex = $items->count() - 1;

        return $items
            ->values()
            ->map(function (array $item, int $index) use (&$remainingDiscount, $discountAmount, $affectedTotal, $lastIndex) {
                $itemDiscount = $index === $lastIndex
                    ? $remainingDiscount
                    : Currency::roundAmount($discountAmount * ($item['line_total'] / $affectedTotal));

                $itemDiscount = Currency::roundAmount(min($itemDiscount, $item['line_total'], $remainingDiscount));
                $remainingDiscount = Currency::roundAmount($remainingDiscount - $itemDiscount);

                return array_merge($this->affectedItemPayload($item), [
                    'discount_amount' => $itemDiscount,
                ]);
            })
            ->all();
    }

    private function affectedItemPayload(array $item): array
    {
        return [
            'type' => $item['type'],
            'id' => $item['id'],
            'category_id' => $item['category_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'line_total' => $item['line_total'],
            'applicable_amount' => $item['line_total'],
        ];
    }

    private function result(
        bool $applicable,
        ?string $reason,
        ?CustomerPromotion $customerPromotion,
        ?Promotion $promotion,
        float $discountAmount,
        float $subtotal,
        array $affectedItems,
        ?float $minimumRequired
    ): array {
        $discountAmount = Currency::roundAmount(min($discountAmount, $subtotal));
        $subtotal = Currency::roundAmount($subtotal);

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
