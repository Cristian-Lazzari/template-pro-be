<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use App\Models\Customer;
use App\Services\Crm\CustomerSegmentService;
use Illuminate\Database\Eloquent\Builder;

class MarketingCustomerSegmentService
{
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

    public function queryForSegment(?string $segment, ?Campaign $campaign = null): Builder
    {
        $consentBasis = $campaign?->consentBasis()
            ?? Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING;

        return $this->queryForSegmentAndConsentBasis($segment, $consentBasis);
    }

    public function queryForSegmentAndConsentBasis(?string $segment, ?string $consentBasis): Builder
    {
        $normalizedSegment = $this->normalizeSegment($segment);
        $query = Customer::query();

        $this->marketingConsentService->applyCampaignAudienceEligibility($query, $consentBasis);

        if ($normalizedSegment !== 'all') {
            $customerIds = $this->customerIdsForSegment($normalizedSegment);

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

        return array_key_exists($segment, self::SEGMENT_OPTIONS)
            ? $segment
            : 'all';
    }

    public function validSegmentKeys(): array
    {
        return array_values(array_unique(array_merge(
            array_keys(self::SEGMENT_OPTIONS),
            array_keys(self::LEGACY_SEGMENT_MAP)
        )));
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

    private function applyDefaultOrder(Builder $query): Builder
    {
        return $query
            ->orderByDesc('last_activity_at')
            ->orderByDesc('created_at');
    }
}
