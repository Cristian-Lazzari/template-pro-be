<?php

namespace App\Services\Marketing;

use App\Models\Campaign;
use Illuminate\Support\Carbon;

class CampaignScheduleService
{
    private const SLOT_MINUTES = 15;
    private const BUFFER_MINUTES = 15;
    private const DEFAULT_DURATION_MINUTES = 60;

    public function normalizeScheduledAt(?string $requestedAt, ?string $window = null, ?Campaign $campaign = null): Carbon
    {
        $window = $window ?: ($requestedAt ? 'custom' : 'next_available');

        if ($window === 'custom') {
            $from = $requestedAt ? Carbon::parse($requestedAt) : $this->getWindowBaseTime('next_available');

            return $this->findNextAvailableSlot(
                $from,
                $this->estimateCampaignDurationMinutes($campaign),
                $campaign?->getKey()
            );
        }

        return $this->findNextAvailableSlot(
            $this->getWindowBaseTime($window),
            $this->estimateCampaignDurationMinutes($campaign),
            $campaign?->getKey()
        );
    }

    public function findNextAvailableSlot(Carbon $from, int $estimatedMinutes = 60, ?int $excludeCampaignId = null): Carbon
    {
        $slot = $this->roundToNextSlot($from->greaterThan(now()) ? $from : now());
        $estimatedMinutes = max(self::SLOT_MINUTES, $estimatedMinutes);
        $campaigns = Campaign::query()
            ->whereIn('status', ['scheduled', 'running', 'active'])
            ->whereNotNull('scheduled_at')
            ->whereNull('sent_at')
            ->when($excludeCampaignId, fn ($query) => $query->whereKeyNot($excludeCampaignId))
            ->orderBy('scheduled_at')
            ->get();

        for ($attempt = 0; $attempt < 500; $attempt++) {
            $candidateEnd = $slot->copy()->addMinutes($estimatedMinutes + self::BUFFER_MINUTES);
            $conflict = null;

            foreach ($campaigns as $campaign) {
                $existingStart = $campaign->scheduled_at;

                if (! $existingStart) {
                    continue;
                }

                $existingEnd = $existingStart->copy()->addMinutes(
                    $this->estimateCampaignDurationMinutes($campaign) + self::BUFFER_MINUTES
                );

                if ($slot->lt($existingEnd) && $candidateEnd->gt($existingStart)) {
                    $conflict = $existingEnd;
                    break;
                }
            }

            if (! $conflict) {
                return $slot;
            }

            $slot = $this->roundToNextSlot($conflict);
        }

        return $slot;
    }

    public function estimateCampaignDurationMinutes(?Campaign $campaign = null, ?int $recipientsCount = null): int
    {
        $recipients = $recipientsCount;

        if ($recipients === null && $campaign?->exists) {
            $recipients = $campaign->customerPromotions()->count();
        }

        if (! $recipients || $recipients < 1) {
            return self::DEFAULT_DURATION_MINUTES;
        }

        $batchLimit = max(1, (int) data_get($campaign?->metadata, 'send_batch_limit', 20));
        $batchInterval = max(1, (int) data_get($campaign?->metadata, 'batch_interval_minutes', 10));
        $duration = (int) ceil($recipients / $batchLimit) * $batchInterval;

        return max(self::SLOT_MINUTES, $duration);
    }

    public function getWindowOptions(): array
    {
        return [
            'next_available' => 'Prima finestra disponibile',
            'today_afternoon' => 'Oggi pomeriggio',
            'today_evening' => 'Oggi sera',
            'tomorrow_morning' => 'Domani mattina',
            'tomorrow_lunch' => 'Domani pranzo',
            'tomorrow_evening' => 'Domani sera',
            'custom' => 'Data personalizzata',
        ];
    }

    public function getWindowBaseTime(string $window): Carbon
    {
        return match ($window) {
            'today_afternoon' => $this->todayOrTomorrowAt(15, 0),
            'today_evening' => $this->todayOrTomorrowAt(18, 0),
            'tomorrow_morning' => now()->copy()->addDay()->setTime(10, 0),
            'tomorrow_lunch' => now()->copy()->addDay()->setTime(12, 0),
            'tomorrow_evening' => now()->copy()->addDay()->setTime(18, 0),
            'next_available' => now()->copy()->addMinutes(self::SLOT_MINUTES),
            default => now()->copy()->addMinutes(self::SLOT_MINUTES),
        };
    }

    private function todayOrTomorrowAt(int $hour, int $minute): Carbon
    {
        $base = now()->copy()->setTime($hour, $minute);

        return $base->isPast() ? $base->addDay() : $base;
    }

    private function roundToNextSlot(Carbon $date): Carbon
    {
        $rounded = $date->copy()->second(0);
        $remainder = $rounded->minute % self::SLOT_MINUTES;

        if ($remainder !== 0) {
            $rounded->addMinutes(self::SLOT_MINUTES - $remainder);
        }

        if ($rounded->lt($date)) {
            $rounded->addMinutes(self::SLOT_MINUTES);
        }

        return $rounded;
    }
}
