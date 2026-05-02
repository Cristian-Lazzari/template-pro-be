<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CampaignAudienceBuilder
{
    private const MAX_CUSTOMERS_LIMIT = 500;

    private ?array $customerColumns = null;

    public function queryForCampaign(Campaign $campaign): Builder
    {
        return $this->queryForSegment($campaign->segment);
    }

    public function countForCampaign(Campaign $campaign): int
    {
        return $this->queryForCampaign($campaign)->count();
    }

    public function getCustomersForCampaign(Campaign $campaign, int $limit = self::MAX_CUSTOMERS_LIMIT): Collection
    {
        $limit = max(1, min($limit, self::MAX_CUSTOMERS_LIMIT));

        return $this->queryForCampaign($campaign)
            ->limit($limit)
            ->get();
    }

    public function queryForSegment(?string $segment): Builder
    {
        $query = Customer::query();

        $this->applyMarketingConsent($query);

        match ($segment) {
            'new_customers' => $this->applyNewCustomersSegment($query),
            'inactive_customers' => $this->applyInactiveCustomersSegment($query),
            'loyal_customers' => $this->applyLoyalCustomersSegment($query),
            'high_spending_customers' => $this->applyHighSpendingCustomersSegment($query),
            default => null,
        };

        return $this->applyDefaultOrder($query);
    }

    private function applyMarketingConsent(Builder $query): void
    {
        if ($this->hasCustomerColumn('marketing_consent_at')) {
            $query->whereNotNull('marketing_consent_at');

            return;
        }

        if ($this->hasCustomerColumn('soft_marketing')) {
            $query->where('soft_marketing', true);

            return;
        }

        if ($this->hasCustomerColumn('news_letter')) {
            $query->where('news_letter', true);

            return;
        }

        $query->whereNull('id')->whereNotNull('id');
    }

    private function applyNewCustomersSegment(Builder $query): void
    {
        $query->where(function (Builder $nested) {
            if ($this->hasCustomerColumn('lifecycle_segment')) {
                $nested->whereIn('lifecycle_segment', ['new', 'new_customers']);
            }

            $this->orWhereInteractionCountAtMost($nested, 1);
        });
    }

    private function applyInactiveCustomersSegment(Builder $query): void
    {
        $query->where(function (Builder $nested) {
            if ($this->hasCustomerColumn('last_activity_at')) {
                $nested->where('last_activity_at', '<', now()->subDays(30));

                $nested->orWhere(function (Builder $withoutActivity) {
                    $withoutActivity->whereNull('last_activity_at');
                    $this->whereInteractionCountAtLeast($withoutActivity, 1);
                });

                return;
            }

            $this->whereInteractionCountAtLeast($nested, 1);
        });
    }

    private function applyLoyalCustomersSegment(Builder $query): void
    {
        $query->where(function (Builder $nested) {
            if ($this->hasCustomerColumn('lifecycle_segment')) {
                $nested->whereIn('lifecycle_segment', ['loyal', 'loyal_customers']);
            }

            $this->orWhereInteractionCountAtLeast($nested, 3);
        });
    }

    private function applyHighSpendingCustomersSegment(Builder $query): void
    {
        $query->where(function (Builder $nested) {
            if ($this->hasCustomerColumn('total_spent')) {
                $nested->where('total_spent', '>=', 100);
            }

            if ($this->hasCustomerColumn('customer_score')) {
                $nested->orWhere('customer_score', '>=', 80);
            }
        });
    }

    private function applyDefaultOrder(Builder $query): Builder
    {
        if ($this->hasCustomerColumn('last_activity_at')) {
            return $query
                ->orderByDesc('last_activity_at')
                ->orderByDesc('created_at');
        }

        return $query->orderByDesc('created_at');
    }

    private function orWhereInteractionCountAtMost(Builder $query, int $max): void
    {
        $query->orWhere(function (Builder $nested) use ($max) {
            $this->whereInteractionCountAtMost($nested, $max);
        });
    }

    private function orWhereInteractionCountAtLeast(Builder $query, int $min): void
    {
        $query->orWhere(function (Builder $nested) use ($min) {
            $this->whereInteractionCountAtLeast($nested, $min);
        });
    }

    private function whereInteractionCountAtMost(Builder $query, int $max): void
    {
        $this->whereInteractionCount($query, '<=', $max);
    }

    private function whereInteractionCountAtLeast(Builder $query, int $min): void
    {
        $this->whereInteractionCount($query, '>=', $min);
    }

    private function whereInteractionCount(Builder $query, string $operator, int $value): void
    {
        if ($this->hasCustomerColumn('orders_count') && $this->hasCustomerColumn('reservations_count')) {
            $query->whereRaw(
                '(COALESCE(customers.orders_count, 0) + COALESCE(customers.reservations_count, 0)) ' . $operator . ' ?',
                [$value]
            );

            return;
        }

        if ($this->hasCustomerColumn('interactions_count')) {
            $query->where('interactions_count', $operator, $value);

            return;
        }

        if ($this->hasCustomerColumn('orders_count')) {
            $query->where('orders_count', $operator, $value);

            return;
        }

        if ($this->hasCustomerColumn('reservations_count')) {
            $query->where('reservations_count', $operator, $value);

            return;
        }

        $query->whereNull('id')->whereNotNull('id');
    }

    private function hasCustomerColumn(string $column): bool
    {
        return array_key_exists($column, $this->customerColumns());
    }

    private function customerColumns(): array
    {
        if ($this->customerColumns !== null) {
            return $this->customerColumns;
        }

        if (! Schema::hasTable('customers')) {
            return $this->customerColumns = [];
        }

        return $this->customerColumns = array_flip(Schema::getColumnListing('customers'));
    }
}
