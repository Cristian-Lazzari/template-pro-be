<?php

namespace App\Console\Commands\Marketing;

use App\Jobs\Marketing\SendMarketingCustomerPromotionEmailJob;
use App\Models\Campaign;
use App\Models\CustomerPromotion;
use App\Services\Marketing\CampaignAssignmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendScheduledCampaignEmailsCommand extends Command
{
    protected $signature = 'marketing:send-scheduled-campaign-emails';

    protected $description = 'Dispatch queued marketing emails for scheduled marketing campaigns.';

    public function handle(CampaignAssignmentService $assignmentService): int
    {
        $now = now();
        $this->normalizeLegacySentCampaigns();

        $campaigns = Campaign::query()
            ->whereIn('status', ['scheduled', 'running', 'active'])
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->whereNull('sent_at')
            ->orderBy('scheduled_at')
            ->limit(20)
            ->get();

        Log::info('Scheduled marketing campaign email command started.', [
            'now' => $now->toDateTimeString(),
            'queue_connection' => config('queue.default'),
            'campaigns_found' => $campaigns->count(),
        ]);

        foreach ($campaigns as $campaign) {
            Log::info('Processing scheduled marketing campaign.', [
                'campaign_id' => $campaign->getKey(),
                'status' => $campaign->status,
                'scheduled_at' => optional($campaign->scheduled_at)->toDateTimeString(),
                'sent_at' => optional($campaign->sent_at)->toDateTimeString(),
            ]);

            $this->completeCampaignIfFullySent($campaign);

            if (in_array($campaign->fresh()?->status, ['completed', 'sent'], true)) {
                Log::info('Scheduled marketing campaign skipped because it is already fully sent.', [
                    'campaign_id' => $campaign->getKey(),
                ]);

                continue;
            }

            if ($this->dispatchAlreadyRunning($campaign, $now)) {
                Log::info('Scheduled marketing campaign skipped because dispatch is already running.', [
                    'campaign_id' => $campaign->getKey(),
                ]);

                continue;
            }

            $this->markDispatchStarted($campaign, $now);

            if (! $campaign->customerPromotions()->exists()) {
                $assignmentResult = $assignmentService->assign($campaign, 500, false);

                Log::info('Scheduled marketing campaign assignments prepared before dispatch.', [
                    'campaign_id' => $campaign->getKey(),
                    'assigned_count' => $assignmentResult['assigned_count'] ?? null,
                    'already_assigned_count' => $assignmentResult['already_assigned_count'] ?? null,
                    'skipped_count' => $assignmentResult['skipped_count'] ?? null,
                    'errors_count' => $assignmentResult['errors_count'] ?? null,
                ]);

                $campaign->refresh();
            }

            $pendingQuery = $this->pendingCustomerPromotions($campaign, $now);
            $pendingCount = (clone $pendingQuery)->count();
            $batchLimit = $this->batchLimit($campaign);
            $pending = $pendingQuery
                ->limit($batchLimit)
                ->get();

            $delaySeconds = $this->delaySeconds($campaign);
            $dispatchedCount = 0;

            Log::info('Scheduled marketing campaign pending emails resolved.', [
                'campaign_id' => $campaign->getKey(),
                'pending_count' => $pendingCount,
                'batch_limit' => $batchLimit,
                'dispatching_count' => $pending->count(),
                'delay_seconds' => $delaySeconds,
            ]);

            foreach ($pending as $index => $customerPromotion) {
                $delay = $index * $delaySeconds;

                $this->markCustomerPromotionQueued($customerPromotion, $now, $delay);

                SendMarketingCustomerPromotionEmailJob::dispatch($customerPromotion->getKey())
                    ->delay($now->copy()->addSeconds($delay));

                Log::info('Scheduled marketing email job dispatched.', [
                    'campaign_id' => $campaign->getKey(),
                    'customer_promotion_id' => $customerPromotion->getKey(),
                    'delay_seconds' => $delay,
                ]);

                $dispatchedCount++;
            }

            $this->markDispatchBatchFinished($campaign, $now, $dispatchedCount);
            $this->completeCampaignIfFullySent($campaign);

            Log::info('Scheduled marketing campaign dispatch completed.', [
                'campaign_id' => $campaign->getKey(),
                'dispatched_count' => $dispatchedCount,
            ]);

            $this->info("Campaign {$campaign->getKey()}: dispatched {$dispatchedCount} marketing email jobs.");
        }

        return self::SUCCESS;
    }

    private function pendingCustomerPromotions(Campaign $campaign, Carbon $now)
    {
        $retryQueuedBefore = $now->copy()->subMinutes(15)->toDateTimeString();

        return CustomerPromotion::query()
            ->where('campaign_id', $campaign->getKey())
            ->whereNull('automation_id')
            ->whereNull('email_sent_at')
            ->whereNull('promo_used')
            ->where(function ($query) use ($retryQueuedBefore) {
                $query->whereNull('metadata->queued_for_send_at')
                    ->orWhere('metadata->queued_for_send_at', '<=', $retryQueuedBefore);
            })
            ->whereHas('customer')
            ->whereHas('promotion')
            ->orderBy('id');
    }

    private function delaySeconds(Campaign $campaign): int
    {
        $value = data_get($campaign->metadata, 'send_delay_seconds', 30);

        return max(0, (int) $value);
    }

    private function batchLimit(Campaign $campaign): int
    {
        $value = data_get($campaign->metadata, 'send_batch_limit', 100);

        return max(1, min(500, (int) $value));
    }

    private function dispatchAlreadyRunning(Campaign $campaign, Carbon $now): bool
    {
        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];
        $startedAt = data_get($metadata, 'dispatch_started_at');
        $completedAt = data_get($metadata, 'dispatch_completed_at');

        if (! $startedAt || $completedAt) {
            return false;
        }

        try {
            return Carbon::parse($startedAt)->diffInSeconds($now) < 60;
        } catch (\Throwable $exception) {
            Log::warning('Invalid marketing campaign dispatch_started_at metadata.', [
                'campaign_id' => $campaign->getKey(),
                'dispatch_started_at' => $startedAt,
            ]);

            return false;
        }
    }

    private function markDispatchStarted(Campaign $campaign, Carbon $now): void
    {
        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];

        if (! data_get($metadata, 'dispatch_started_at')) {
            $metadata['dispatch_started_at'] = $now->toDateTimeString();
        }

        if (in_array($campaign->status, ['scheduled', 'active'], true)) {
            unset($metadata['dispatch_completed_at']);
        }

        $campaign->forceFill([
            'status' => 'running',
            'metadata' => $metadata,
        ])->save();
    }

    private function markDispatchBatchFinished(Campaign $campaign, Carbon $now, int $dispatchedCount): void
    {
        $campaign->refresh();

        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];
        $metadata['last_dispatch_batch_at'] = $now->toDateTimeString();
        $metadata['dispatch_count'] = (int) ($metadata['dispatch_count'] ?? 0) + $dispatchedCount;

        $campaign->forceFill(['metadata' => $metadata])->save();
    }

    private function markCustomerPromotionQueued(CustomerPromotion $customerPromotion, Carbon $now, int $delaySeconds): void
    {
        $metadata = is_array($customerPromotion->metadata) ? $customerPromotion->metadata : [];
        $metadata['queued_for_send_at'] = $now->toDateTimeString();
        $metadata['queued_send_delay_seconds'] = $delaySeconds;

        $customerPromotion->forceFill(['metadata' => $metadata])->save();
    }

    private function completeCampaignIfFullySent(Campaign $campaign): bool
    {
        $campaign->refresh();

        if ($campaign->status === 'sent') {
            $campaign->forceFill(['status' => 'completed'])->save();

            return true;
        }

        if ($campaign->sent_at !== null && $campaign->status !== 'completed') {
            $campaign->forceFill(['status' => 'completed'])->save();

            return true;
        }

        $totalEmails = $campaign->customerPromotions()->count();

        if ($totalEmails === 0) {
            return false;
        }

        $sentEmails = $campaign->customerPromotions()
            ->whereNotNull('email_sent_at')
            ->count();

        if ($sentEmails < $totalEmails) {
            return false;
        }

        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];
        $metadata['dispatch_completed_at'] = now()->toDateTimeString();
        $metadata['send_completed_at'] = now()->toDateTimeString();

        $campaign->forceFill([
            'status' => 'completed',
            'sent_at' => now(),
            'metadata' => $metadata,
        ])->save();

        Log::info('Scheduled marketing campaign marked as sent.', [
            'campaign_id' => $campaign->getKey(),
        ]);

        return true;
    }

    private function normalizeLegacySentCampaigns(): void
    {
        $updated = Campaign::query()
            ->where('status', 'sent')
            ->update(['status' => 'completed']);

        if ($updated > 0) {
            Log::info('Legacy sent marketing campaigns normalized to completed.', [
                'updated_count' => $updated,
            ]);
        }
    }
}
