<?php

namespace App\Services\Marketing;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class MarketingConsentService
{
    private ?array $customerColumns = null;

    public function applyEmailMarketingConsent(Builder $query): Builder
    {
        if ($this->hasCustomerColumn('email_marketing_consent_at')) {
            $emailMarketingColumn = $this->qualifyCustomerColumn($query, 'email_marketing_consent_at');

            return $query->where(function (Builder $nested) use ($query, $emailMarketingColumn) {
                $nested->whereNotNull($emailMarketingColumn);

                if ($this->hasCustomerColumn('marketing_consent_at')) {
                    $legacyMarketingColumn = $this->qualifyCustomerColumn($query, 'marketing_consent_at');

                    $nested->orWhere(function (Builder $legacy) use ($query, $emailMarketingColumn, $legacyMarketingColumn) {
                        $legacy
                            ->whereNull($emailMarketingColumn)
                            ->whereNotNull($legacyMarketingColumn);

                        if ($this->hasCustomerColumn('consents_updated_at')) {
                            $legacy->whereNull($this->qualifyCustomerColumn($query, 'consents_updated_at'));
                        }
                    });
                }
            });
        }

        if ($this->hasCustomerColumn('marketing_consent_at')) {
            return $query->whereNotNull($this->qualifyCustomerColumn($query, 'marketing_consent_at'));
        }

        return $query->whereNull($this->qualifyCustomerColumn($query, 'id'))->whereNotNull($this->qualifyCustomerColumn($query, 'id'));
    }

    public function customerHasEmailMarketingConsent(?Customer $customer): bool
    {
        if (! $customer) {
            return false;
        }

        if ($customer->email_marketing_consent_at !== null) {
            return true;
        }

        return $customer->marketing_consent_at !== null
            && $customer->consents_updated_at === null;
    }

    public function customerHasTrackingConsent(?Customer $customer): bool
    {
        return $customer?->tracking_consent_at !== null;
    }

    private function qualifyCustomerColumn(Builder $query, string $column): string
    {
        return $query->getModel()->qualifyColumn($column);
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
