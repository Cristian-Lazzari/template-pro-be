<?php

namespace App\Console\Commands\Marketing;

use App\Models\Campaign;
use App\Models\CustomerPromotion;
use App\Services\Marketing\CampaignAssignmentService;
use App\Services\Marketing\MarketingEmailDispatchService;
use App\Services\Marketing\MarketingRunMarkerService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessScheduledCampaignEmailsCommand extends Command
{
    protected $signature = 'marketing:process-scheduled-campaign-emails
        {--limit=20 : Maximum customer promotions to process in this run}
        {--campaign_id= : Process only a specific campaign id}
        {--dry-run : Show what would be processed without sending or writing campaign metadata}';

    protected $description = 'Process scheduled marketing campaign emails directly, without queue workers.';

    public function handle(
        CampaignAssignmentService $assignmentService,
        MarketingEmailDispatchService $dispatchService,
        MarketingRunMarkerService $runMarkerService
    ): int {
        $now = now();
        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');
        $remainingLimit = $limit;
        $summary = $this->emptySummary($dryRun, $limit);

        $campaigns = $this->campaignQuery($now)
            ->when($this->option('campaign_id'), fn ($query, $campaignId) => $query->whereKey($campaignId))
            ->limit(20)
            ->get();

        $summary['campaigns_found'] = $campaigns->count();

        Log::info('Shared-hosting marketing campaign processor started.', [
            'dry_run' => $dryRun,
            'limit' => $limit,
            'campaign_id' => $this->option('campaign_id') ?: null,
            'campaigns_found' => $campaigns->count(),
        ]);

        foreach ($campaigns as $campaign) {
            if ($remainingLimit <= 0) {
                break;
            }

            if ($this->shouldWaitForNextBatch($campaign, $now)) {
                $summary['skipped_campaigns_count']++;
                $summary['campaigns'][] = $this->campaignReport($campaign, 'skipped', 'Waiting for next batch window.');

                continue;
            }

            $summary['campaigns_checked']++;

            if ($dryRun) {
                $report = $this->dryRunCampaign($campaign, $assignmentService, $dispatchService, $remainingLimit);
                $remainingLimit -= max($report['checked_count'], $report['would_send_count']);
                $summary['would_send_count'] += $report['would_send_count'];
                $summary['skipped_count'] += $report['skipped_count'];
                $summary['errors_count'] += $report['errors_count'];
                $summary['campaigns'][] = $report;

                continue;
            }

            $report = $this->processCampaign($campaign, $assignmentService, $dispatchService, $now, $remainingLimit);
            $remainingLimit -= $report['checked_count'];
            $summary['sent_count'] += $report['sent_count'];
            $summary['skipped_count'] += $report['skipped_count'];
            $summary['errors_count'] += $report['errors_count'];
            $summary['campaigns'][] = $report;
        }

        $marker = $runMarkerService->refresh();
        $summary['marker'] = $marker;

        $this->line(json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }

    private function campaignQuery(Carbon $now)
    {
        return Campaign::query()
            ->whereIn('status', ['scheduled', 'running', 'active'])
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->whereNull('sent_at')
            ->orderBy('scheduled_at')
            ->orderBy('id');
    }

    private function dryRunCampaign(
        Campaign $campaign,
        CampaignAssignmentService $assignmentService,
        MarketingEmailDispatchService $dispatchService,
        int $limit
    ): array {
        if (! $campaign->customerPromotions()->exists()) {
            $preview = $assignmentService->preview($campaign);
            $wouldSendCount = min($limit, (int) ($preview['assignable_count'] ?? 0));

            return array_merge($this->campaignReport($campaign, 'dry_run'), [
                'checked_count' => 0,
                'would_send_count' => $wouldSendCount,
                'skipped_count' => (int) ($preview['skipped_count'] ?? 0),
                'errors_count' => (int) ($preview['errors_count'] ?? 0),
                'note' => 'No assignments exist yet; dry-run used assignment preview.',
            ]);
        }

        $checkedCount = 0;
        $wouldSendCount = 0;
        $skippedCount = 0;

        foreach ($this->pendingCustomerPromotions($campaign)->limit($limit)->get() as $customerPromotion) {
            $checkedCount++;
            $sendResult = $dispatchService->sendCustomerPromotion($customerPromotion, true);

            if ($sendResult['can_send'] ?? false) {
                $wouldSendCount++;
            } else {
                $skippedCount++;
            }
        }

        return array_merge($this->campaignReport($campaign, 'dry_run'), [
            'checked_count' => $checkedCount,
            'would_send_count' => $wouldSendCount,
            'skipped_count' => $skippedCount,
            'errors_count' => 0,
        ]);
    }

    private function processCampaign(
        Campaign $campaign,
        CampaignAssignmentService $assignmentService,
        MarketingEmailDispatchService $dispatchService,
        Carbon $now,
        int $limit
    ): array {
        $this->markRunning($campaign, $now);

        if (! $campaign->customerPromotions()->exists()) {
            $assignmentResult = $assignmentService->assign($campaign, 500, false);

            Log::info('Shared-hosting marketing processor prepared assignments.', [
                'campaign_id' => $campaign->getKey(),
                'assigned_count' => $assignmentResult['assigned_count'] ?? null,
                'already_assigned_count' => $assignmentResult['already_assigned_count'] ?? null,
                'skipped_count' => $assignmentResult['skipped_count'] ?? null,
                'errors_count' => $assignmentResult['errors_count'] ?? null,
            ]);

            $campaign->refresh();
        }

        $report = array_merge($this->campaignReport($campaign, 'write'), [
            'checked_count' => 0,
            'sent_count' => 0,
            'skipped_count' => 0,
            'errors_count' => 0,
            'errors' => [],
        ]);

        $customerPromotions = $this->pendingCustomerPromotions($campaign)
            ->limit($limit)
            ->get();

        foreach ($customerPromotions as $customerPromotion) {
            $report['checked_count']++;

            try {
                $sendResult = $dispatchService->sendCustomerPromotion($customerPromotion, false);

                if ($sendResult['sent'] ?? false) {
                    $report['sent_count']++;

                    continue;
                }

                $report['skipped_count']++;
                $this->addError($report, $customerPromotion, $sendResult['failure_reason'] ?? 'Email was not sent.');
            } catch (Throwable $exception) {
                $report['skipped_count']++;
                $this->addError($report, $customerPromotion, $exception->getMessage());
                report($exception);
            }
        }

        $this->updateCampaignAfterBatch($campaign, $now, $report['sent_count']);

        return $report;
    }

    private function pendingCustomerPromotions(Campaign $campaign)
    {
        return CustomerPromotion::query()
            ->with(['campaign.model', 'customer', 'promotion'])
            ->where('campaign_id', $campaign->getKey())
            ->whereNull('automation_id')
            ->whereNull('email_sent_at')
            ->whereHas('customer')
            ->whereHas('promotion')
            ->orderBy('id');
    }

    private function markRunning(Campaign $campaign, Carbon $now): void
    {
        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];

        if (! data_get($metadata, 'dispatch_started_at')) {
            $metadata['dispatch_started_at'] = $now->toDateTimeString();
        }

        $campaign->forceFill([
            'status' => 'running',
            'metadata' => $metadata,
        ])->save();
    }

    private function updateCampaignAfterBatch(Campaign $campaign, Carbon $now, int $sentCount): void
    {
        $campaign->refresh();

        $total = $campaign->customerPromotions()->count();
        $sent = $campaign->customerPromotions()->whereNotNull('email_sent_at')->count();
        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];
        $metadata['last_processed_at'] = $now->toDateTimeString();
        $metadata['last_batch_sent_count'] = $sentCount;

        if ($total > 0 && $sent >= $total) {
            $metadata['completed_at'] = $now->toDateTimeString();
            $metadata['dispatch_completed_at'] = $now->toDateTimeString();
            unset($metadata['next_batch_due_at']);

            $campaign->forceFill([
                'status' => 'completed',
                'sent_at' => $now,
                'metadata' => $metadata,
            ])->save();

            return;
        }

        if ($total > $sent) {
            $metadata['next_batch_due_at'] = $now->copy()
                ->addMinutes($this->batchIntervalMinutes($campaign))
                ->toDateTimeString();
        }

        $campaign->forceFill([
            'status' => 'running',
            'metadata' => $metadata,
        ])->save();
    }

    private function shouldWaitForNextBatch(Campaign $campaign, Carbon $now): bool
    {
        if ($campaign->status !== 'running') {
            return false;
        }

        $nextBatchDueAt = data_get($campaign->metadata, 'next_batch_due_at');

        if (! $nextBatchDueAt) {
            return false;
        }

        try {
            return Carbon::parse($nextBatchDueAt)->gt($now);
        } catch (Throwable) {
            return false;
        }
    }

    private function batchIntervalMinutes(Campaign $campaign): int
    {
        return max(1, (int) data_get($campaign->metadata, 'batch_interval_minutes', 5));
    }

    private function emptySummary(bool $dryRun, int $limit): array
    {
        return [
            'mode' => $dryRun ? 'dry_run' : 'write',
            'limit' => $limit,
            'campaigns_found' => 0,
            'campaigns_checked' => 0,
            'skipped_campaigns_count' => 0,
            'would_send_count' => 0,
            'sent_count' => 0,
            'skipped_count' => 0,
            'errors_count' => 0,
            'campaigns' => [],
            'marker' => null,
        ];
    }

    private function campaignReport(Campaign $campaign, string $mode, ?string $skipReason = null): array
    {
        return [
            'mode' => $mode,
            'campaign_id' => $campaign->getKey(),
            'status' => $campaign->status,
            'scheduled_at' => optional($campaign->scheduled_at)->toDateTimeString(),
            'skip_reason' => $skipReason,
        ];
    }

    private function addError(array &$report, CustomerPromotion $customerPromotion, string $message): void
    {
        $report['errors_count']++;

        if (count($report['errors']) >= 20) {
            return;
        }

        $report['errors'][] = [
            'customer_promotion_id' => $customerPromotion->getKey(),
            'message' => $message,
        ];
    }
}
