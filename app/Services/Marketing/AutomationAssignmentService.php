<?php

namespace App\Services\Marketing;

use App\Models\Automation;
use App\Models\CustomerPromotion;
use Throwable;

class AutomationAssignmentService
{
    private const MAX_ERRORS = 20;

    public function __construct(
        private AutomationAudienceBuilder $audienceBuilder,
        private CustomerPromotionService $customerPromotionService,
        private PromotionEligibilityService $eligibilityService
    ) {
    }

    public function preview(Automation $automation, int $limit = 500): array
    {
        $result = $this->assign($automation, $limit, true);
        $result['mode'] = 'preview';

        return $result;
    }

    public function assign(Automation $automation, int $limit = 500, bool $dryRun = true): array
    {
        $result = $this->baseAssignmentReport($automation, $dryRun ? 'dry_run' : 'write');

        $failureReason = $this->getFailureReason($automation);

        if ($failureReason !== null) {
            $result['failure_reason'] = $failureReason;

            return $result;
        }

        $promotions = $this->automationPromotions($automation);
        $customers = $this->audienceBuilder->getCustomersForAutomation($automation, $limit);

        $result['can_assign'] = true;
        $result['customers_checked'] = $customers->count();
        $result['promotions_count'] = $promotions->count();

        foreach ($customers as $customer) {
            foreach ($promotions as $promotion) {
                try {
                    if ($this->hasOpenAssignment($customer->getKey(), $promotion->getKey(), $automation->getKey())) {
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
                        null,
                        $automation,
                        $this->assignmentMetadata($automation)
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

        return $result;
    }

    public function canAssign(Automation $automation): bool
    {
        return $this->getFailureReason($automation) === null;
    }

    public function getFailureReason(Automation $automation): ?string
    {
        if (! $automation->exists) {
            return 'Automation must be persisted before assignment.';
        }

        if (! in_array($automation->status, ['draft', 'active'], true)) {
            return 'Automation status must be draft or active.';
        }

        return $this->audienceBuilder->getFailureReason($automation);
    }

    private function baseAssignmentReport(Automation $automation, string $mode): array
    {
        return [
            'mode' => $mode,
            'automation_id' => $automation->getKey(),
            'trigger' => $automation->trigger,
            'can_assign' => false,
            'failure_reason' => null,
            'customers_checked' => 0,
            'promotions_count' => $automation->exists ? $automation->promotions()->count() : 0,
            'assigned_count' => 0,
            'already_assigned_count' => 0,
            'skipped_count' => 0,
            'errors_count' => 0,
            'errors' => [],
        ];
    }

    private function automationPromotions(Automation $automation)
    {
        return $automation->promotions()
            ->orderBy('promotions.id')
            ->get();
    }

    private function hasOpenAssignment($customerId, $promotionId, $automationId): bool
    {
        return CustomerPromotion::query()
            ->where('customer_id', $customerId)
            ->where('promotion_id', $promotionId)
            ->where('automation_id', $automationId)
            ->whereNull('campaign_id')
            ->whereNull('promo_used')
            ->exists();
    }

    private function assignmentMetadata(Automation $automation): array
    {
        return [
            'source' => 'automation_assignment',
            'automation_trigger' => $automation->trigger,
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
