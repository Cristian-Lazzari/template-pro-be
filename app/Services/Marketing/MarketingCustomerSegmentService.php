<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use App\Models\Customer;
use App\Services\Crm\CustomerSegmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class MarketingCustomerSegmentService
{
    private const BASE_SEGMENT_OPTIONS = [
        'reservations' => 'Prenotazioni',
        'orders' => 'Ordini',
        'both' => 'Prenotazioni e ordini',
    ];

    private const SEGMENT_OPTIONS = [
        'all' => 'Tutti i clienti con consenso marketing',
        'new_customers' => 'Nuovi clienti',
        'active_customers' => 'Clienti attivi',
        'loyal_customers' => 'Clienti fedeli',
        'at_risk_customers' => 'Clienti a rischio',
        'lost_customers' => 'Clienti persi',
        'high_value_customers' => 'Clienti alto valore',
        'reservation_only' => 'Solo prenotazioni',
        'order_only' => 'Solo ordini',
        'habit_customers' => 'Clienti abituali',
        'low_engagement' => 'Basso coinvolgimento',
    ];

    private const LEGACY_SEGMENT_MAP = [
        'inactive_customers' => 'at_risk_customers',
        'high_spending_customers' => 'high_value_customers',
    ];

    public function __construct(
        private CustomerSegmentService $customerSegmentService,
        private MarketingConsentService $marketingConsentService
    ) {
    }

    public function getSegmentOptions(): array
    {
        return self::SEGMENT_OPTIONS;
    }

    public function getBaseSegmentOptions(): array
    {
        return self::BASE_SEGMENT_OPTIONS;
    }

    public function getAdvancedSegmentOptions(): array
    {
        return self::SEGMENT_OPTIONS;
    }

    public function queryForSegment(?string $segment, ?Campaign $campaign = null): Builder
    {
        $consentBasis = $campaign?->consentBasis()
            ?? Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING;

        return $this->queryForSegmentAndConsentBasis($segment, $consentBasis, $campaign?->campaignType());
    }

    public function queryForSegmentAndConsentBasis(?string $segment, ?string $consentBasis, ?string $campaignType = null): Builder
    {
        $normalizedSegment = $campaignType === null
            ? $this->normalizeSegment($segment)
            : $this->normalizeSegmentForCampaignType($segment, $campaignType);
        $query = Customer::query();

        $this->marketingConsentService->applyCampaignAudienceEligibility($query, $consentBasis);

        if (Campaign::normalizeCampaignType($campaignType) === Campaign::CAMPAIGN_TYPE_PROFILING) {
            $this->applyProfilingConsent($query);
        }

        if ($normalizedSegment !== 'all') {
            $customerIds = $this->isBaseSegment($normalizedSegment)
                ? $this->customerIdsForBaseSegment($normalizedSegment)
                : $this->customerIdsForSegment($normalizedSegment);

            if ($customerIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereKey($customerIds);
            }
        }

        return $this->applyDefaultOrder($query);
    }

    public function isValidSegment(?string $segment): bool
    {
        $segment = trim((string) $segment);

        return $segment === ''
            || array_key_exists($segment, self::BASE_SEGMENT_OPTIONS)
            || array_key_exists($segment, self::SEGMENT_OPTIONS)
            || array_key_exists($segment, self::LEGACY_SEGMENT_MAP);
    }

    public function normalizeSegment(?string $segment): string
    {
        $segment = trim((string) $segment);

        if ($segment === '') {
            return 'all';
        }

        $segment = self::LEGACY_SEGMENT_MAP[$segment] ?? $segment;

        return array_key_exists($segment, self::BASE_SEGMENT_OPTIONS)
            || array_key_exists($segment, self::SEGMENT_OPTIONS)
            ? $segment
            : 'all';
    }

    public function normalizeSegmentForCampaignType(?string $segment, ?string $campaignType): string
    {
        $campaignType = Campaign::normalizeCampaignType($campaignType);
        $segment = self::LEGACY_SEGMENT_MAP[trim((string) $segment)] ?? trim((string) $segment);

        if ($campaignType === Campaign::CAMPAIGN_TYPE_PROFILING) {
            return array_key_exists($segment, self::SEGMENT_OPTIONS) ? $segment : 'all';
        }

        return array_key_exists($segment, self::BASE_SEGMENT_OPTIONS)
            ? $segment
            : array_key_first(self::BASE_SEGMENT_OPTIONS);
    }

    public function validSegmentKeys(): array
    {
        return array_values(array_unique(array_merge(
            array_keys(self::BASE_SEGMENT_OPTIONS),
            array_keys(self::SEGMENT_OPTIONS),
            array_keys(self::LEGACY_SEGMENT_MAP)
        )));
    }

    public function validSegmentKeysForCampaignType(?string $campaignType): array
    {
        if (Campaign::normalizeCampaignType($campaignType) === Campaign::CAMPAIGN_TYPE_PROFILING) {
            return array_values(array_unique(array_merge(
                array_keys(self::SEGMENT_OPTIONS),
                array_keys(self::LEGACY_SEGMENT_MAP)
            )));
        }

        return array_keys(self::BASE_SEGMENT_OPTIONS);
    }

    private function customerIdsForSegment(string $segment): array
    {
        return $this->customerSegmentService
            ->getCustomersForSegment($segment)
            ->pluck('customer_id')
            ->filter()
            ->map(fn ($customerId) => (int) $customerId)
            ->unique()
            ->values()
            ->all();
    }

    private function customerIdsForBaseSegment(string $segment): array
    {
        return $this->customerSegmentService
            ->buildBaseCustomerQuery(['type' => $segment])
            ->pluck('customer_id')
            ->filter()
            ->map(fn ($customerId) => (int) $customerId)
            ->unique()
            ->values()
            ->all();
    }

    private function isBaseSegment(string $segment): bool
    {
        return array_key_exists($segment, self::BASE_SEGMENT_OPTIONS);
    }

    private function applyProfilingConsent(Builder $query): Builder
    {
        if (! Schema::hasColumn('customers', 'profiling_consent_at')) {
            return $query;
        }

        return $query->whereNotNull($query->getModel()->qualifyColumn('profiling_consent_at'));
    }

    private function applyDefaultOrder(Builder $query): Builder
    {
        return $query
            ->orderByDesc('last_activity_at')
            ->orderByDesc('created_at');
    }
}
