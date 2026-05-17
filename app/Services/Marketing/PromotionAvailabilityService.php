<?php

namespace App\Services\Marketing;

use App\Models\Promotion;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class PromotionAvailabilityService
{
    public function isAvailable(Promotion $promotion, CarbonInterface|string|null $dateTime = null): bool
    {
        return $this->unavailableReason($promotion, $dateTime) === null;
    }

    public function unavailableReason(Promotion $promotion, CarbonInterface|string|null $dateTime = null): ?string
    {
        $dateTime = $this->resolveDateTime($dateTime);

        if (! $this->matchesWeekday($promotion, $dateTime)) {
            return 'promotion_not_available_on_day';
        }

        if (! $this->matchesTime($promotion, $dateTime)) {
            return 'promotion_not_available_at_time';
        }

        return null;
    }

    public function payload(Promotion $promotion, CarbonInterface|string|null $dateTime = null): array
    {
        $validWeekdays = $this->validWeekdays($promotion);
        $fromTime = $this->normalizeTime($promotion->valid_from_time);
        $toTime = $this->normalizeTime($promotion->valid_to_time);

        return [
            'valid_weekdays' => $validWeekdays,
            'valid_from_time' => $fromTime,
            'valid_to_time' => $toTime,
            'has_limits' => $validWeekdays !== null || $fromTime !== null || $toTime !== null,
            'is_available_now' => $this->isAvailable($promotion, $dateTime),
            'unavailable_reason' => $this->unavailableReason($promotion, $dateTime),
        ];
    }

    public function resolveDateTime(CarbonInterface|string|null $dateTime): Carbon
    {
        if ($dateTime instanceof CarbonInterface) {
            return Carbon::instance($dateTime->toDateTime())->setTimezone(config('app.timezone'));
        }

        if (is_string($dateTime) && trim($dateTime) !== '') {
            $value = trim($dateTime);

            foreach (['Y-m-d H:i', 'd/m/Y H:i', 'Y-m-d\TH:i'] as $format) {
                try {
                    return Carbon::createFromFormat($format, $value, config('app.timezone'));
                } catch (\Throwable) {
                    // Try the next known checkout format.
                }
            }

            try {
                return Carbon::parse($value, config('app.timezone'));
            } catch (\Throwable) {
                return now();
            }
        }

        return now();
    }

    private function matchesWeekday(Promotion $promotion, CarbonInterface $dateTime): bool
    {
        $validWeekdays = $this->validWeekdays($promotion);

        if ($validWeekdays === null) {
            return true;
        }

        return in_array((int) $dateTime->isoWeekday(), $validWeekdays, true);
    }

    private function matchesTime(Promotion $promotion, CarbonInterface $dateTime): bool
    {
        $from = $this->timeToMinutes($promotion->valid_from_time);
        $to = $this->timeToMinutes($promotion->valid_to_time);

        if ($from === null && $to === null) {
            return true;
        }

        $current = ((int) $dateTime->format('H')) * 60 + (int) $dateTime->format('i');

        if ($from !== null && $to !== null) {
            return $from <= $to
                ? $current >= $from && $current <= $to
                : $current >= $from || $current <= $to;
        }

        if ($from !== null) {
            return $current >= $from;
        }

        return $current <= $to;
    }

    private function validWeekdays(Promotion $promotion): ?array
    {
        $weekdays = $promotion->valid_weekdays;

        if (! is_array($weekdays)) {
            return null;
        }

        $weekdays = collect($weekdays)
            ->map(fn ($day) => (int) $day)
            ->filter(fn ($day) => $day >= 1 && $day <= 7)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return count($weekdays) > 0 && count($weekdays) < 7 ? $weekdays : null;
    }

    private function timeToMinutes($value): ?int
    {
        $time = $this->normalizeTime($value);

        if ($time === null) {
            return null;
        }

        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return $hours * 60 + $minutes;
    }

    private function normalizeTime($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('H:i');
        }

        $value = trim((string) $value);

        if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?$/', $value, $matches) !== 1) {
            return null;
        }

        return $matches[1] . ':' . $matches[2];
    }
}
