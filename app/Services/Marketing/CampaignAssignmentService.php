<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use App\Models\CustomerPromotion;
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

                    $result['assignable_count']++;
                } catch (Throwable $exception) {
                    $this->addError($result, $customer->getKey(), $promotion->getKey(), $exception);
                }
            }
        }

        return $result;
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

                    if ($dryRun) {
                        $result['assigned_count']++;

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
                        $result['assigned_count']++;
                    } else {
                        $result['already_assigned_count']++;
                    }
                } catch (Throwable $exception) {
                    $result['skipped_count']++;
                    $this->addError($result, $customer->getKey(), $promotion->getKey(), $exception);
                }
            }
        }

        if (! $dryRun && $result['assigned_count'] > 0 && $campaign->status === 'draft') {
            $campaign->update(['status' => 'active']);
        }

        return $result;
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

        if (! in_array($campaign->status, ['draft', 'active'], true)) {
            return 'Campaign status must be draft or active.';
        }

        if (! $campaign->promotions()->exists()) {
            return 'Campaign must have at least one promotion.';
        }

        return null;
    }

    private function basePreviewReport(Campaign $campaign): array
    {
        return [
            'mode' => 'preview',
            'campaign_id' => $campaign->getKey(),
            'can_assign' => false,
            'failure_reason' => null,
            'customers_checked' => 0,
            'promotions_count' => $campaign->exists ? $campaign->promotions()->count() : 0,
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
