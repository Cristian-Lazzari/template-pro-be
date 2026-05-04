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

    protected $description = 'Dispatch queued marketing emails for active scheduled campaigns.';

    public function handle(CampaignAssignmentService $assignmentService): int
    {
        $now = now();
        $campaigns = Campaign::query()
            ->where('status', 'active')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->whereNull('sent_at')
            ->orderBy('scheduled_at')
            ->limit(20)
            ->get();

        foreach ($campaigns as $campaign) {
            $this->completeCampaignIfFullySent($campaign);

            if ($campaign->fresh()?->status === 'sent') {
                continue;
            }

            if ($this->dispatchAlreadyRunning($campaign, $now)) {
                continue;
            }

            $this->markDispatchStarted($campaign, $now);

            if (! $campaign->customerPromotions()->exists()) {
                $assignmentService->assign($campaign, 500, false);
                $campaign->refresh();
            }

            $pending = $this->pendingCustomerPromotions($campaign)
                ->limit($this->batchLimit($campaign))
                ->get();

            $delaySeconds = $this->delaySeconds($campaign);
            $dispatchedCount = 0;

            foreach ($pending as $index => $customerPromotion) {
                $delay = $index * $delaySeconds;

                $this->markCustomerPromotionQueued($customerPromotion, $now, $delay);

                SendMarketingCustomerPromotionEmailJob::dispatch($customerPromotion->getKey())
                    ->delay($now->copy()->addSeconds($delay));

                $dispatchedCount++;
            }

            $this->markDispatchCompleted($campaign, $now, $dispatchedCount);
            $this->completeCampaignIfFullySent($campaign);

            $this->info("Campaign {$campaign->getKey()}: dispatched {$dispatchedCount} marketing email jobs.");
        }

        return self::SUCCESS;
    }

    private function pendingCustomerPromotions(Campaign $campaign)
    {
        return CustomerPromotion::query()
            ->where('campaign_id', $campaign->getKey())
            ->whereNull('automation_id')
            ->whereNull('email_sent_at')
            ->whereNull('promo_used')
            ->whereNull('metadata->queued_for_send_at')
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
        $metadata['dispatch_started_at'] = $now->toDateTimeString();
        unset($metadata['dispatch_completed_at']);

        $campaign->forceFill(['metadata' => $metadata])->save();
    }

    private function markDispatchCompleted(Campaign $campaign, Carbon $now, int $dispatchedCount): void
    {
        $campaign->refresh();

        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];
        $metadata['dispatch_completed_at'] = $now->toDateTimeString();
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

        if ($campaign->sent_at !== null || ! $campaign->customerPromotions()->exists()) {
            return false;
        }

        $hasPendingEmails = $campaign->customerPromotions()
            ->whereNull('email_sent_at')
            ->exists();

        if ($hasPendingEmails) {
            return false;
        }

        $metadata = is_array($campaign->metadata) ? $campaign->metadata : [];
        $metadata['send_completed_at'] = now()->toDateTimeString();

        $campaign->forceFill([
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => $metadata,
        ])->save();

        return true;
    }
}
