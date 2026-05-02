<?php

namespace App\Services\Marketing;

use App\Models\Automation;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CustomerPromotionService
{
    private const STATUS_ASSIGNED = 'assigned';
    private const STATUS_SENT = 'sent';
    private const STATUS_OPENED = 'opened';
    private const STATUS_CLICKED = 'clicked';
    private const STATUS_USED = 'used';

    private const STATUS_RANKS = [
        self::STATUS_ASSIGNED => 0,
        self::STATUS_SENT => 1,
        self::STATUS_OPENED => 2,
        self::STATUS_CLICKED => 3,
        self::STATUS_USED => 4,
    ];

    public function __construct(private PromotionEligibilityService $eligibilityService)
    {
    }

    public function assignToCustomer(
        Customer $customer,
        Promotion $promotion,
        ?Campaign $campaign = null,
        ?Automation $automation = null,
        array $metadata = []
    ): CustomerPromotion {
        $this->ensurePersisted($customer, 'Customer');
        $this->ensurePersisted($promotion, 'Promotion');
        $this->ensurePersisted($campaign, 'Campaign');
        $this->ensurePersisted($automation, 'Automation');
        $this->ensureSingleOrigin($campaign, $automation);

        return DB::transaction(function () use ($customer, $promotion, $campaign, $automation, $metadata) {
            $promotion->refresh();

            $failureReason = $this->eligibilityService->getFailureReason($promotion, $customer);

            if ($failureReason !== null) {
                throw new InvalidArgumentException($failureReason);
            }

            $existing = $this->matchingAssignmentQuery($customer, $promotion, $campaign, $automation)
                ->whereNull('promo_used')
                ->where('status', '!=', self::STATUS_USED)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $customerPromotion = CustomerPromotion::query()->create([
                'customer_id' => $customer->getKey(),
                'promotion_id' => $promotion->getKey(),
                'campaign_id' => $campaign?->getKey(),
                'automation_id' => $automation?->getKey(),
                'tracking_token' => $this->makeTrackingToken(),
                'status' => self::STATUS_ASSIGNED,
                'metadata' => $metadata,
            ]);

            $this->incrementAssignmentCounters($promotion, $campaign, $automation);

            return $customerPromotion;
        });
    }

    public function markSent(CustomerPromotion $customerPromotion, bool $updateCustomerContact = true): CustomerPromotion
    {
        $this->ensurePersisted($customerPromotion, 'CustomerPromotion');

        return DB::transaction(function () use ($customerPromotion, $updateCustomerContact) {
            $customerPromotion = $this->lockCustomerPromotion($customerPromotion);
            $wasUnsent = $customerPromotion->email_sent_at === null;
            $now = now();

            if ($wasUnsent) {
                $customerPromotion->email_sent_at = $now;
            }

            $customerPromotion->status = $this->advanceStatus($customerPromotion->status, self::STATUS_SENT);
            $customerPromotion->save();

            if ($wasUnsent) {
                $this->incrementSentCounters($customerPromotion);

                if ($updateCustomerContact) {
                    $customerPromotion->customer()->update([
                        'last_marketing_contact_at' => $now,
                    ]);
                }
            }

            return $customerPromotion->refresh();
        });
    }

    public function markOpened(CustomerPromotion $customerPromotion): CustomerPromotion
    {
        $this->ensurePersisted($customerPromotion, 'CustomerPromotion');

        return DB::transaction(function () use ($customerPromotion) {
            $customerPromotion = $this->lockCustomerPromotion($customerPromotion);

            if ($customerPromotion->email_open_at === null) {
                $customerPromotion->email_open_at = now();
            }

            $customerPromotion->status = $this->advanceStatus($customerPromotion->status, self::STATUS_OPENED);
            $customerPromotion->save();

            return $customerPromotion->refresh();
        });
    }

    public function markClicked(CustomerPromotion $customerPromotion): CustomerPromotion
    {
        $this->ensurePersisted($customerPromotion, 'CustomerPromotion');

        return DB::transaction(function () use ($customerPromotion) {
            $customerPromotion = $this->lockCustomerPromotion($customerPromotion);

            if ($customerPromotion->email_click_at === null) {
                $customerPromotion->email_click_at = now();
            }

            $customerPromotion->status = $this->advanceStatus($customerPromotion->status, self::STATUS_CLICKED);
            $customerPromotion->save();

            return $customerPromotion->refresh();
        });
    }

    public function markUsed(
        CustomerPromotion $customerPromotion,
        ?float $discountAmount = null,
        ?int $orderId = null,
        ?int $reservationId = null
    ): CustomerPromotion {
        $this->ensurePersisted($customerPromotion, 'CustomerPromotion');

        return DB::transaction(function () use ($customerPromotion, $discountAmount, $orderId, $reservationId) {
            $customerPromotion = $this->lockCustomerPromotion($customerPromotion);
            $wasUnused = $customerPromotion->promo_used === null;

            if ($wasUnused) {
                $customerPromotion->promo_used = now();
            }

            $customerPromotion->status = self::STATUS_USED;

            if ($discountAmount !== null) {
                $customerPromotion->discount_amount = $discountAmount;
            }

            if ($orderId !== null) {
                $customerPromotion->order_id = $orderId;
            }

            if ($reservationId !== null) {
                $customerPromotion->reservation_id = $reservationId;
            }

            $customerPromotion->save();

            if ($wasUnused) {
                $customerPromotion->promotion()->increment('total_used');
            }

            return $customerPromotion->refresh();
        });
    }

    private function matchingAssignmentQuery(
        Customer $customer,
        Promotion $promotion,
        ?Campaign $campaign,
        ?Automation $automation
    ): Builder {
        $query = CustomerPromotion::query()
            ->where('customer_id', $customer->getKey())
            ->where('promotion_id', $promotion->getKey());

        $this->whereNullableKey($query, 'campaign_id', $campaign?->getKey());
        $this->whereNullableKey($query, 'automation_id', $automation?->getKey());

        return $query;
    }

    private function whereNullableKey(Builder $query, string $column, $value): void
    {
        if ($value === null) {
            $query->whereNull($column);

            return;
        }

        $query->where($column, $value);
    }

    private function makeTrackingToken(): string
    {
        do {
            $token = (string) Str::uuid();
        } while (CustomerPromotion::query()->where('tracking_token', $token)->exists());

        return $token;
    }

    private function incrementAssignmentCounters(Promotion $promotion, ?Campaign $campaign, ?Automation $automation): void
    {
        $promotion->increment('total_activation');

        if ($campaign) {
            $campaign->increment('total_activation');

            DB::table('campaign_promotion')
                ->where('campaign_id', $campaign->getKey())
                ->where('promotion_id', $promotion->getKey())
                ->increment('total_activation');
        }

        if ($automation) {
            $automation->increment('total_activation');

            DB::table('automation_promotion')
                ->where('automation_id', $automation->getKey())
                ->where('promotion_id', $promotion->getKey())
                ->increment('total_activation');
        }
    }

    private function incrementSentCounters(CustomerPromotion $customerPromotion): void
    {
        $customerPromotion->promotion()->increment('total_sent');

        if ($customerPromotion->campaign_id) {
            $customerPromotion->campaign()->increment('total_sent');

            DB::table('campaign_promotion')
                ->where('campaign_id', $customerPromotion->campaign_id)
                ->where('promotion_id', $customerPromotion->promotion_id)
                ->increment('total_sent');
        }

        if ($customerPromotion->automation_id) {
            $customerPromotion->automation()->increment('total_sent');

            DB::table('automation_promotion')
                ->where('automation_id', $customerPromotion->automation_id)
                ->where('promotion_id', $customerPromotion->promotion_id)
                ->increment('total_sent');
        }
    }

    private function lockCustomerPromotion(CustomerPromotion $customerPromotion): CustomerPromotion
    {
        return CustomerPromotion::query()
            ->whereKey($customerPromotion->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function advanceStatus(?string $currentStatus, string $targetStatus): string
    {
        $currentRank = self::STATUS_RANKS[$currentStatus] ?? -1;
        $targetRank = self::STATUS_RANKS[$targetStatus];

        return $currentRank >= $targetRank ? (string) $currentStatus : $targetStatus;
    }

    private function ensurePersisted($model, string $name): void
    {
        if ($model !== null && ! $model->exists) {
            throw new InvalidArgumentException($name . ' must be persisted before using marketing promotion services.');
        }
    }

    private function ensureSingleOrigin(?Campaign $campaign, ?Automation $automation): void
    {
        if ($campaign && $automation) {
            throw new InvalidArgumentException('A customer promotion can be linked to either a campaign or an automation, not both.');
        }
    }
}
