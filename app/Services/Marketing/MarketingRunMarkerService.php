<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class MarketingRunMarkerService
{
    public function refresh(): array
    {
        $now = now();
        $campaigns = Campaign::query()
            ->whereIn('status', ['scheduled', 'running', 'active'])
            ->whereNotNull('scheduled_at')
            ->whereNull('sent_at')
            ->get(['id', 'status', 'scheduled_at', 'metadata']);

        if ($campaigns->isEmpty()) {
            $data = $this->markerData(false, null, $now);
            $this->write($data);

            return $data;
        }

        $nextDueAt = $campaigns
            ->map(fn (Campaign $campaign) => $this->nextDueAtForCampaign($campaign, $now))
            ->filter()
            ->sortBy(fn (Carbon $date) => $date->getTimestamp())
            ->first();

        $data = $this->markerData($nextDueAt !== null, $nextDueAt, $now);
        $this->write($data);

        return $data;
    }

    public function write(array $data): void
    {
        File::ensureDirectoryExists(dirname($this->path()));

        File::put(
            $this->path(),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public function path(): string
    {
        return storage_path('app/marketing/next-run.json');
    }

    private function nextDueAtForCampaign(Campaign $campaign, Carbon $now): ?Carbon
    {
        $nextBatchDueAt = $this->parseDate(data_get($campaign->metadata, 'next_batch_due_at'));

        if ($campaign->status === 'running' && $nextBatchDueAt?->isFuture()) {
            return $nextBatchDueAt;
        }

        if ($campaign->scheduled_at?->isFuture()) {
            return $campaign->scheduled_at;
        }

        return $now;
    }

    private function parseDate($value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function markerData(bool $hasPendingCampaigns, ?Carbon $nextDueAt, Carbon $generatedAt): array
    {
        return [
            'has_pending_campaigns' => $hasPendingCampaigns,
            'next_due_at' => $nextDueAt?->toIso8601String(),
            'generated_at' => $generatedAt->toIso8601String(),
        ];
    }
}
