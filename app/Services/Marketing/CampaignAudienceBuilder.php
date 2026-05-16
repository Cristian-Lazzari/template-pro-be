<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CampaignAudienceBuilder
{
    private const MAX_CUSTOMERS_LIMIT = 500;

    public function __construct(
        private MarketingCustomerSegmentService $segmentService,
        private MarketingConsentService $marketingConsentService
    ) {
    }

    public function queryForCampaign(Campaign $campaign): Builder
    {
        return $this->segmentService->queryForSegmentAndConsentBasis(
            $campaign->segment,
            $campaign->consentBasis(),
            $campaign->campaign_type === null ? null : $campaign->campaignType()
        );
    }

    public function countForCampaign(Campaign $campaign): int
    {
        return $this->queryForCampaign($campaign)->count();
    }

    public function availableForCampaign(Campaign $campaign): int
    {
        return $this->segmentService
            ->queryAvailableForCampaignType($campaign->campaignType(), $campaign->consentBasis())
            ->count();
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
        return $this->segmentService->queryForSegment($segment);
    }

    public function queryForSegmentAndConsentBasis(?string $segment, ?string $consentBasis, ?string $campaignType = null): Builder
    {
        return $this->segmentService->queryForSegmentAndConsentBasis($segment, $consentBasis, $campaignType);
    }

    public function countForSegmentAndConsentBasis(?string $segment, ?string $consentBasis, ?string $campaignType = null): int
    {
        return $this->queryForSegmentAndConsentBasis($segment, $consentBasis, $campaignType)->count();
    }

    public function contactableTotalForConsentBasis(?string $consentBasis): int
    {
        $query = Customer::query();
        $this->marketingConsentService->applyContactRequirement($query, $consentBasis);

        return $query->count();
    }

    public function availabilityForConsentBasis(?string $consentBasis): array
    {
        $normalizedConsentBasis = Campaign::normalizeConsentBasis($consentBasis);

        return [
            'eligible' => $this->countForSegmentAndConsentBasis('all', $normalizedConsentBasis),
            'total' => $this->contactableTotalForConsentBasis($normalizedConsentBasis),
            'label' => $this->availabilityLabel($normalizedConsentBasis),
            'total_label' => $this->availabilityTotalLabel($normalizedConsentBasis),
        ];
    }

    public function availabilityByConsentBasis(): array
    {
        $availability = [];

        foreach (Campaign::consentBasisValues() as $consentBasis) {
            $availability[$consentBasis] = $this->availabilityForConsentBasis($consentBasis);
        }

        return $availability;
    }

    public function matrixForSegments(array $segments): array
    {
        $matrix = [];

        foreach (Campaign::consentBasisValues() as $consentBasis) {
            foreach ($segments as $segment) {
                $normalizedSegment = $this->segmentService->normalizeSegment($segment);
                $matrix[$consentBasis][$normalizedSegment] = $this->countForSegmentAndConsentBasis(
                    $normalizedSegment,
                    $consentBasis
                );
            }
        }

        return $matrix;
    }

    private function availabilityLabel(string $consentBasis): string
    {
        return match ($consentBasis) {
            Campaign::CONSENT_BASIS_SOFT_EMAIL_MARKETING => 'Soft email marketing',
            Campaign::CONSENT_BASIS_WHATSAPP_MARKETING => 'WhatsApp marketing',
            default => 'Email marketing esplicito',
        };
    }

    private function availabilityTotalLabel(string $consentBasis): string
    {
        return $consentBasis === Campaign::CONSENT_BASIS_WHATSAPP_MARKETING
            ? 'Totale: clienti con telefono'
            : 'Totale: clienti con email valida';
    }
}
