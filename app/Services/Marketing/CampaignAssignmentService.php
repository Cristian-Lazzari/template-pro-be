<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use App\Models\CustomerPromotion;
use App\Models\Promotion;
use Illuminate\Support\Collection;
use Throwable;

class CampaignAssignmentService
{
    private const MAX_ERRORS = 20;

    public function __construct(
        private CampaignAudienceBuilder $audienceBuilder,
        private CustomerPromotionService $customerPromotionService,
        private PromotionEligibilityService $eligibilityService
    ) {
    }

    public function preview(Campaign $campaign, int $limit = 500): array
    {
        $result = $this->basePreviewReport($campaign);
        $result = $this->withAudienceAvailability($campaign, $result);

        $failureReason = $this->getFailureReason($campaign);

        if ($failureReason !== null) {
            $result['failure_reason'] = $failureReason;

            return $result;
        }

        $promotions = $this->campaignPromotions($campaign);
        $customers = $this->audienceBuilder->getCustomersForCampaign($campaign, $limit);

        $result['can_assign'] = true;
        $result['customers_checked'] = $customers->count();
        $result['promotions_count'] = $promotions->count();

        return $this->evaluateAssignments($campaign, $customers, $promotions, $result, 'assignable_count', false, false);
    }

    public function previewSelection(Campaign $campaign, array $promotionIds, int $limit = 500): array
    {
        $promotionIds = collect($promotionIds)
            ->filter(fn ($promotionId) => filled($promotionId))
            ->map(fn ($promotionId) => (int) $promotionId)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $promotions = $promotionIds === []
            ? collect()
            : Promotion::query()
                ->whereKey($promotionIds)
                ->orderBy('id')
                ->get();
        $result = $this->basePreviewReport($campaign, $promotions->count());
        $result = $this->withAudienceAvailability($campaign, $result);

        if ($promotions->isEmpty()) {
            $result['failure_reason'] = 'Campaign must have at least one promotion.';

            return $result;
        }

        $customers = $this->audienceBuilder->getCustomersForCampaign($campaign, $limit);

        $result['can_assign'] = true;
        $result['customers_checked'] = $customers->count();

        return $this->evaluateAssignments($campaign, $customers, $promotions, $result, 'assignable_count', false, false);
    }

    public function assign(Campaign $campaign, int $limit = 500, bool $dryRun = true): array
    {
        $result = $this->baseAssignmentReport($campaign, $dryRun ? 'dry_run' : 'write');

        $failureReason = $this->getFailureReason($campaign);

        if ($failureReason !== null) {
            $result['failure_reason'] = $failureReason;

            return $result;
        }

        $promotions = $this->campaignPromotions($campaign);
        $customers = $this->audienceBuilder->getCustomersForCampaign($campaign, $limit);

        $result['can_assign'] = true;
        $result['customers_checked'] = $customers->count();
        $result['promotions_count'] = $promotions->count();

        return $this->evaluateAssignments($campaign, $customers, $promotions, $result, 'assigned_count', ! $dryRun, true);
    }

    public function canAssign(Campaign $campaign): bool
    {
        return $this->getFailureReason($campaign) === null;
    }

    public function getFailureReason(Campaign $campaign): ?string
    {
        if (! $campaign->exists) {
            return 'Campaign must be persisted before assignment.';
        }

        if (! in_array($campaign->status, ['draft', 'scheduled', 'running', 'active'], true)) {
            return 'Campaign status must be draft, scheduled or running.';
        }

        if (! $campaign->promotions()->exists()) {
            return 'Campaign must have at least one promotion.';
        }

        return null;
    }

    private function basePreviewReport(Campaign $campaign, ?int $promotionsCount = null): array
    {
        return [
            'mode' => 'preview',
            'campaign_id' => $campaign->exists ? $campaign->getKey() : null,
            'can_assign' => false,
            'failure_reason' => null,
            'customers_checked' => 0,
            'promotions_count' => $promotionsCount ?? ($campaign->exists ? $campaign->promotions()->count() : 0),
            'available_count' => 0,
            'available' => 0,
            'matched_count' => 0,
            'matched' => 0,
            'assignable_count' => 0,
            'already_assigned_count' => 0,
            'skipped_count' => 0,
            'errors_count' => 0,
            'errors' => [],
        ];
    }

    private function baseAssignmentReport(Campaign $campaign, string $mode): array
    {
        return [
            'mode' => $mode,
            'campaign_id' => $campaign->getKey(),
            'can_assign' => false,
            'failure_reason' => null,
            'customers_checked' => 0,
            'promotions_count' => $campaign->exists ? $campaign->promotions()->count() : 0,
            'assigned_count' => 0,
            'already_assigned_count' => 0,
            'skipped_count' => 0,
            'errors_count' => 0,
            'errors' => [],
        ];
    }

    private function campaignPromotions(Campaign $campaign)
    {
        return $campaign->promotions()
            ->orderBy('promotions.id')
            ->get();
    }

    private function hasOpenAssignment($customerId, $promotionId, $campaignId): bool
    {
        if ($campaignId === null) {
            return false;
        }

        return CustomerPromotion::query()
            ->where('customer_id', $customerId)
            ->where('promotion_id', $promotionId)
            ->where('campaign_id', $campaignId)
            ->whereNull('automation_id')
            ->whereNull('promo_used')
            ->exists();
    }

    private function assignmentMetadata(Campaign $campaign): array
    {
        return [
            'source' => 'campaign_assignment',
            'campaign_segment' => $campaign->segment,
            'assigned_by_service' => true,
        ];
    }

    private function withAudienceAvailability(Campaign $campaign, array $result): array
    {
        $result['available_count'] = $this->audienceBuilder->availableForCampaign($campaign);
        $result['available'] = $result['available_count'];

        return $result;
    }

    private function evaluateAssignments(
        Campaign $campaign,
        Collection $customers,
        Collection $promotions,
        array $result,
        string $successKey,
        bool $writeAssignments,
        bool $countErrorsAsSkipped
    ): array {
        $matchedCustomerIds = [];

        foreach ($customers as $customer) {
            foreach ($promotions as $promotion) {
                try {
                    if ($this->hasOpenAssignment($customer->getKey(), $promotion->getKey(), $campaign->getKey())) {
                        $result['already_assigned_count']++;

                        continue;
                    }

                    $failure = $this->eligibilityService->getFailureReason($promotion, $customer);

                    if ($failure !== null) {
                        $result['skipped_count']++;

                        continue;
                    }

                    $matchedCustomerIds[(int) $customer->getKey()] = true;

                    if (! $writeAssignments) {
                        $result[$successKey]++;

                        continue;
                    }

                    $customerPromotion = $this->customerPromotionService->assignToCustomer(
                        $customer,
                        $promotion,
                        $campaign,
                        null,
                        $this->assignmentMetadata($campaign)
                    );

                    if ($customerPromotion->wasRecentlyCreated) {
                        $result[$successKey]++;
                    } else {
                        $result['already_assigned_count']++;
                    }
                } catch (Throwable $exception) {
                    if ($countErrorsAsSkipped) {
                        $result['skipped_count']++;
                    }

                    $this->addError($result, $customer->getKey(), $promotion->getKey(), $exception);
                }
            }
        }

        if (array_key_exists('matched_count', $result)) {
            $result['matched_count'] = count($matchedCustomerIds);
            $result['matched'] = $result['matched_count'];
        }

        return $result;
    }

    private function addError(array &$result, $customerId, $promotionId, Throwable $exception): void
    {
        $result['errors_count']++;

        if (count($result['errors']) >= self::MAX_ERRORS) {
            return;
        }

        $result['errors'][] = [
            'customer_id' => $customerId,
            'promotion_id' => $promotionId,
            'message' => $exception->getMessage(),
        ];
    }
}
