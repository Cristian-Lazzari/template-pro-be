<?php

namespace App\Services\Marketing;

use App\Models\Automation;
use App\Models\Campaign;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Builder;

class MarketingReportService
{
    public function forPromotion(Promotion $promotion): array
    {
        return $this->buildReportFromQuery(
            CustomerPromotion::query()->where('promotion_id', $promotion->getKey())
        );
    }

    public function forCampaign(Campaign $campaign): array
    {
        return $this->buildReportFromQuery(
            CustomerPromotion::query()->where('campaign_id', $campaign->getKey())
        );
    }

    public function forAutomation(Automation $automation): array
    {
        return $this->buildReportFromQuery(
            CustomerPromotion::query()->where('automation_id', $automation->getKey())
        );
    }

    public function global(?array $filters = []): array
    {
        return $this->buildReportFromQuery(
            $this->applyFilters(CustomerPromotion::query(), $filters ?? [])
        );
    }

    private function buildReportFromQuery(Builder $query): array
    {
        $involvedCount = (clone $query)->count();
        $sentCount = (clone $query)->whereNotNull('email_sent_at')->count();
        $openedCount = (clone $query)->whereNotNull('email_open_at')->count();
        $clickedCount = (clone $query)->whereNotNull('email_click_at')->count();
        $usedCount = (clone $query)->whereNotNull('promo_used')->count();
        $orderConversionCount = (clone $query)->whereNotNull('order_id')->count();
        $reservationConversionCount = (clone $query)->whereNotNull('reservation_id')->count();
        $discountTotal = (float) (clone $query)->sum('discount_amount');

        return [
            'involved_count' => $involvedCount,
            'sent_count' => $sentCount,
            'opened_count' => $openedCount,
            'clicked_count' => $clickedCount,
            'used_count' => $usedCount,
            'order_conversion_count' => $orderConversionCount,
            'reservation_conversion_count' => $reservationConversionCount,
            'discount_total' => round($discountTotal, 2),
            'open_rate' => $this->percentage($openedCount, $sentCount),
            'click_rate' => $this->percentage($clickedCount, $sentCount),
            'usage_rate' => $this->percentage($usedCount, $sentCount),
        ];
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['promotion_id']) && $filters['promotion_id'] !== '') {
            $query->where('promotion_id', $filters['promotion_id']);
        }

        if (isset($filters['campaign_id']) && $filters['campaign_id'] !== '') {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['automation_id']) && $filters['automation_id'] !== '') {
            $query->where('automation_id', $filters['automation_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from']) && $filters['from'] !== '') {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to']) && $filters['to'] !== '') {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query;
    }

    private function percentage(int $value, int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($value / $total) * 100, 2);
    }
}
