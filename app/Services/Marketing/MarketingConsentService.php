<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class MarketingConsentService
{
    private ?array $customerColumns = null;

    public function applyEmailMarketingConsent(Builder $query): Builder
    {
        return $this->applyExplicitEmailMarketingConsent($query);
    }

    public function applyCampaignConsent(Builder $query, Campaign $campaign): Builder
    {
        return $this->applyConsentBasis($query, $campaign->consentBasis());
    }

    public function applyConsentBasis(Builder $query, ?string $consentBasis): Builder
    {
        return match (Campaign::normalizeConsentBasis($consentBasis)) {
            Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING => $this->applySoftEmailMarketingConsent($query),
            Campaign::CONSENT_BASIS_WHATSAPP_MARKETING => $this->applyWhatsappMarketingConsent($query),
            default => $this->applyExplicitEmailMarketingConsent($query),
        };
    }

    public function applyContactRequirement(Builder $query, ?string $consentBasis): Builder
    {
        return match (Campaign::normalizeConsentBasis($consentBasis)) {
            Campaign::CONSENT_BASIS_WHATSAPP_MARKETING => $this->applyPhoneContactRequirement($query),
            default => $this->applyEmailContactRequirement($query),
        };
    }

    public function applyCampaignAudienceEligibility(Builder $query, ?string $consentBasis): Builder
    {
        $this->applyContactRequirement($query, $consentBasis);

        return $this->applyConsentBasis($query, $consentBasis);
    }

    public function applyExplicitEmailMarketingConsent(Builder $query): Builder
    {
        if ($this->hasCustomerColumn('email_marketing_consent_at')) {
            return $query->whereNotNull($this->qualifyCustomerColumn($query, 'email_marketing_consent_at'));
        }

        return $query->whereNull($this->qualifyCustomerColumn($query, 'id'))->whereNotNull($this->qualifyCustomerColumn($query, 'id'));
    }

    public function applySoftEmailMarketingConsent(Builder $query): Builder
    {
        if ($this->hasCustomerColumn('soft_email_marketing_unsubscribed_at')) {
            return $query->whereNull($this->qualifyCustomerColumn($query, 'soft_email_marketing_unsubscribed_at'));
        }

        return $query;
    }

    public function applyWhatsappMarketingConsent(Builder $query): Builder
    {
        if ($this->hasCustomerColumn('whatsapp_marketing_consent_at')) {
            return $query->whereNotNull($this->qualifyCustomerColumn($query, 'whatsapp_marketing_consent_at'));
        }

        return $query->whereNull($this->qualifyCustomerColumn($query, 'id'))->whereNotNull($this->qualifyCustomerColumn($query, 'id'));
    }

    public function customerHasExplicitEmailMarketingConsent(?Customer $customer): bool
    {
        if (! $customer) {
            return false;
        }

        return $customer->email_marketing_consent_at !== null;
    }

    public function customerHasEmailMarketingConsent(?Customer $customer): bool
    {
        return $this->customerHasExplicitEmailMarketingConsent($customer);
    }

    public function customerAllowsSoftEmailMarketing(?Customer $customer): bool
    {
        return $customer !== null
            && $customer->soft_email_marketing_unsubscribed_at === null;
    }

    public function customerHasWhatsappMarketingConsent(?Customer $customer): bool
    {
        return $customer?->whatsapp_marketing_consent_at !== null;
    }

    public function customerCanReceiveCampaign(?Customer $customer, Campaign $campaign): bool
    {
        return match ($campaign->consentBasis()) {
            Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING => $this->customerAllowsSoftEmailMarketing($customer),
            Campaign::CONSENT_BASIS_WHATSAPP_MARKETING => $this->customerHasWhatsappMarketingConsent($customer),
            default => $this->customerHasExplicitEmailMarketingConsent($customer),
        };
    }

    public function customerHasTrackingConsent(?Customer $customer): bool
    {
        return $customer?->tracking_consent_at !== null;
    }

    private function qualifyCustomerColumn(Builder $query, string $column): string
    {
        return $query->getModel()->qualifyColumn($column);
    }

    private function applyEmailContactRequirement(Builder $query): Builder
    {
        if (! $this->hasCustomerColumn('email')) {
            return $this->emptyQuery($query);
        }

        $emailColumn = $this->qualifyCustomerColumn($query, 'email');

        return $this->whereFilled($query, $emailColumn)
            ->where($emailColumn, 'like', '_%@_%._%');
    }

    private function applyPhoneContactRequirement(Builder $query): Builder
    {
        if (! $this->hasCustomerColumn('phone')) {
            return $this->emptyQuery($query);
        }

        return $this->whereFilled($query, $this->qualifyCustomerColumn($query, 'phone'));
    }

    private function whereFilled(Builder $query, string $column): Builder
    {
        $wrappedColumn = $query->getQuery()->getGrammar()->wrap($column);

        return $query
            ->whereNotNull($column)
            ->whereRaw("TRIM({$wrappedColumn}) <> ?", ['']);
    }

    private function emptyQuery(Builder $query): Builder
    {
        return $query
            ->whereNull($this->qualifyCustomerColumn($query, 'id'))
            ->whereNotNull($this->qualifyCustomerColumn($query, 'id'));
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
