<?php

namespace App\Services\Marketing;

use App\Models\Automation;
use App\Models\CustomerPromotion;
use Illuminate\Support\Collection;
use Throwable;

class AutomationAssignmentService
{
    private const MAX_ERRORS = 20;

    private const ANNUAL_TRIGGERS = ['birthday_before', 'customer_anniversary'];
    private const LIFECYCLE_TRIGGERS = ['first_order_completed', 'first_booking_completed'];

    private const DEFAULT_COOLDOWN_ANNUAL = 300;
    private const DEFAULT_COOLDOWN_LIFECYCLE = 365;
    private const DEFAULT_COOLDOWN_GENERAL = 30;

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

        $promotions   = $this->automationPromotions($automation);
        $customers    = $this->audienceBuilder->getCustomersForAutomation($automation, $limit);
        $cooldownDays = $this->resolveCooldownDays($automation);
        $blockedSet   = $this->buildCooldownBlockedSet($customers, $automation, $cooldownDays);

        $result['can_assign']         = true;
        $result['customers_checked']  = $customers->count();
        $result['promotions_count']   = $promotions->count();
        $result['cooldown_days']      = $cooldownDays;

        foreach ($customers as $customer) {
            if (array_key_exists($customer->getKey(), $blockedSet)) {
                $result['cooldown_skipped_count']++;

                continue;
            }

            foreach ($promotions as $promotion) {
                try {
                    $openAssignment = $this->findOpenAssignment(
                        $customer->getKey(),
                        $promotion->getKey(),
                        $automation->getKey()
                    );

                    if ($openAssignment !== null) {
                        // Promozione aperta trovata: non duplicare mai.
                        // Se siamo qui, il cooldown è scaduto (il buildCooldownBlockedSet non ha
                        // trovato questa CustomerPromotion nella finestra di cooldown).
                        // Valutiamo se flaggare per reminder.
                        $canFlag = $openAssignment->reminder_eligible_at === null
                            && $openAssignment->reminder_sent_at === null;

                        if ($canFlag) {
                            // Prima volta che il cooldown scade con la promo ancora aperta:
                            // flagga come candidata al reminder email.
                            if (! $dryRun) {
                                $openAssignment->reminder_eligible_at = now();
                                $openAssignment->save();
                            }
                            $result['reminder_flagged_count']++;
                        } else {
                            // Reminder già flaggato o già inviato: non fare nulla.
                            $result['already_assigned_count']++;
                        }

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

    public function resolveCooldownDays(Automation $automation): int
    {
        $metadata = is_array($automation->metadata) ? $automation->metadata : [];

        if (isset($metadata['cooldown_days']) && is_int($metadata['cooldown_days']) && $metadata['cooldown_days'] >= 0) {
            return $metadata['cooldown_days'];
        }

        if (in_array($automation->trigger, self::ANNUAL_TRIGGERS, true)) {
            return self::DEFAULT_COOLDOWN_ANNUAL;
        }

        if (in_array($automation->trigger, self::LIFECYCLE_TRIGGERS, true)) {
            return self::DEFAULT_COOLDOWN_LIFECYCLE;
        }

        return self::DEFAULT_COOLDOWN_GENERAL;
    }

    private function buildCooldownBlockedSet(Collection $customers, Automation $automation, int $cooldownDays): array
    {
        if ($customers->isEmpty() || $cooldownDays <= 0) {
            return [];
        }

        $customerIds = $customers->pluck('id')->all();
        $since       = now()->subDays($cooldownDays);

        $blocked = CustomerPromotion::query()
            ->select('customer_id')
            ->whereIn('customer_id', $customerIds)
            ->where('automation_id', $automation->getKey())
            ->where('created_at', '>=', $since)
            ->distinct()
            ->pluck('customer_id')
            ->all();

        return array_flip($blocked);
    }

    private function baseAssignmentReport(Automation $automation, string $mode): array
    {
        return [
            'mode'                   => $mode,
            'automation_id'          => $automation->getKey(),
            'trigger'                => $automation->trigger,
            'can_assign'             => false,
            'failure_reason'         => null,
            'customers_checked'      => 0,
            'promotions_count'       => $automation->exists ? $automation->promotions()->count() : 0,
            'cooldown_days'          => 0,
            'assigned_count'         => 0,
            'already_assigned_count' => 0,
            'cooldown_skipped_count' => 0,
            'reminder_flagged_count' => 0,
            'skipped_count'          => 0,
            'errors_count'           => 0,
            'errors'                 => [],
        ];
    }

    private function automationPromotions(Automation $automation)
    {
        return $automation->promotions()
            ->orderBy('promotions.id')
            ->get();
    }

    /**
     * Cerca una CustomerPromotion aperta (non usata) per la tripletta
     * customer + promotion + automation, generata da automazione (campaign_id null).
     *
     * Restituisce il modello se trovato, null altrimenti.
     * Usata per: (1) evitare duplicati, (2) flaggare per reminder quando il cooldown scade.
     */
    private function findOpenAssignment($customerId, $promotionId, $automationId): ?CustomerPromotion
    {
        return CustomerPromotion::query()
            ->where('customer_id', $customerId)
            ->where('promotion_id', $promotionId)
            ->where('automation_id', $automationId)
            ->whereNull('campaign_id')
            ->whereNull('promo_used')
            ->first();
    }

    private function assignmentMetadata(Automation $automation): array
    {
        return [
            'source'              => 'automation_assignment',
            'automation_trigger'  => $automation->trigger,
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
            'customer_id'  => $customerId,
            'promotion_id' => $promotionId,
            'message'      => $exception->getMessage(),
        ];
    }
}
