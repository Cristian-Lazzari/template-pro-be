<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CampaignAudienceBuilder
{
    private const MAX_CUSTOMERS_LIMIT = 500;

    public function __construct(
        private MarketingCustomerSegmentService $segmentService
    ) {
    }

    public function queryForCampaign(Campaign $campaign): Builder
    {
        return $this->segmentService->queryForSegment($campaign->segment, $campaign);
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
        return $this->segmentService->queryForSegment($segment);
    }
}
