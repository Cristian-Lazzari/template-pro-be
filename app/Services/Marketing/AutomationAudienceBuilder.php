<?php

namespace App\Services\Marketing;

use App\Models\Automation;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AutomationAudienceBuilder
{
    private const MAX_CUSTOMERS_LIMIT = 500;

    private const SUPPORTED_TRIGGERS = [
        'order_inactive_30_days',
        'reservation_inactive_30_days',
        'birthday',
        'first_order_completed',
        'abandoned_profile',
    ];

    private ?array $customerColumns = null;

    public function queryForAutomation(Automation $automation): Builder
    {
        $query = Customer::query();

        $this->applyMarketingConsent($query);

        match ($automation->trigger) {
            'order_inactive_30_days' => $this->applyOrderInactiveTrigger($query),
            'reservation_inactive_30_days' => $this->applyReservationInactiveTrigger($query),
            'first_order_completed' => $this->applyFirstOrderCompletedTrigger($query),
            'abandoned_profile' => $this->applyAbandonedProfileTrigger($query),
            'birthday' => $this->applyBirthdayTrigger($query),
            default => $this->applyEmptyResult($query),
        };

        return $this->applyDefaultOrder($query);
    }

    public function countForAutomation(Automation $automation): int
    {
        return $this->queryForAutomation($automation)->count();
    }

    public function getCustomersForAutomation(Automation $automation, int $limit = self::MAX_CUSTOMERS_LIMIT): Collection
    {
        $limit = max(1, min($limit, self::MAX_CUSTOMERS_LIMIT));

        return $this->queryForAutomation($automation)
            ->limit($limit)
            ->get();
    }

    public function getFailureReason(Automation $automation): ?string
    {
        if ($automation->status === 'archived') {
            return 'Automazione archiviata.';
        }

        if (! $automation->trigger) {
            return 'Trigger mancante.';
        }

        if (! in_array($automation->trigger, self::SUPPORTED_TRIGGERS, true)) {
            return 'Trigger non supportato per la preview audience.';
        }

        if ($automation->trigger === 'birthday' && ! $this->hasBirthDateColumn()) {
            return 'Trigger compleanno non disponibile: customers non ha un campo data nascita.';
        }

        if ($automation->promotions()->count() === 0) {
            return 'Nessuna promozione collegata.';
        }

        return null;
    }

    private function applyMarketingConsent(Builder $query): void
    {
        if ($this->hasCustomerColumn('marketing_consent_at')) {
            $query->whereNotNull('marketing_consent_at');

            return;
        }

        $this->applyEmptyResult($query);
    }

    private function applyOrderInactiveTrigger(Builder $query): void
    {
        if (! $this->hasCustomerColumn('last_activity_at') || ! $this->hasCustomerColumn('orders_count')) {
            $this->applyEmptyResult($query);

            return;
        }

        $query
            ->where('last_activity_at', '<', now()->subDays(30))
            ->where('orders_count', '>', 0);
    }

    private function applyReservationInactiveTrigger(Builder $query): void
    {
        if (! $this->hasCustomerColumn('last_activity_at') || ! $this->hasCustomerColumn('reservations_count')) {
            $this->applyEmptyResult($query);

            return;
        }

        $query
            ->where('last_activity_at', '<', now()->subDays(30))
            ->where('reservations_count', '>', 0);
    }

    private function applyFirstOrderCompletedTrigger(Builder $query): void
    {
        if (! $this->hasCustomerColumn('orders_count')) {
            $this->applyEmptyResult($query);

            return;
        }

        $query->where('orders_count', 1);
    }

    private function applyAbandonedProfileTrigger(Builder $query): void
    {
        if (! $this->hasCustomerColumn('profiling_consent_at') && ! $this->hasCustomerColumn('lifecycle_segment')) {
            $this->applyEmptyResult($query);

            return;
        }

        $query->where(function (Builder $nested) {
            if ($this->hasCustomerColumn('profiling_consent_at')) {
                $nested->whereNull('profiling_consent_at');
            }

            if ($this->hasCustomerColumn('lifecycle_segment')) {
                $nested->orWhere('lifecycle_segment', 'abandoned_profile');
            }
        });
    }

    private function applyBirthdayTrigger(Builder $query): void
    {
        $birthDateColumn = $this->birthDateColumn();

        if ($birthDateColumn === null) {
            $this->applyEmptyResult($query);

            return;
        }

        $today = now();

        $query
            ->whereMonth($birthDateColumn, $today->month)
            ->whereDay($birthDateColumn, $today->day);
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

    private function applyEmptyResult(Builder $query): void
    {
        $query->whereNull('id')->whereNotNull('id');
    }

    private function hasBirthDateColumn(): bool
    {
        return $this->birthDateColumn() !== null;
    }

    private function birthDateColumn(): ?string
    {
        foreach (['birth_date', 'birthday', 'date_of_birth', 'dob'] as $column) {
            if ($this->hasCustomerColumn($column)) {
                return $column;
            }
        }

        return null;
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
