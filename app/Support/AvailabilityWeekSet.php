<?php

namespace App\Support;

class AvailabilityWeekSet
{
    public static function normalize($weekSet): array
    {
        $normalized = self::emptyWeek();

        if (!is_array($weekSet)) {
            return $normalized;
        }

        for ($day = 1; $day <= 7; $day++) {
            $slots = $weekSet[$day] ?? $weekSet[(string) $day] ?? [];

            if (!is_array($slots)) {
                continue;
            }

            foreach ($slots as $time => $services) {
                if (!is_string($time) || trim($time) === '') {
                    continue;
                }

                $normalized[$day][$time] = self::normalizeServices($services);
            }

            ksort($normalized[$day]);
        }

        return $normalized;
    }

    private static function emptyWeek(): array
    {
        return [
            1 => [],
            2 => [],
            3 => [],
            4 => [],
            5 => [],
            6 => [],
            7 => [],
        ];
    }

    private static function normalizeServices($services): array
    {
        if (!is_array($services)) {
            $services = $services === null || $services === '' ? [] : [$services];
        }

        $normalized = [];

        foreach ($services as $service) {
            if ($service === null || $service === '') {
                continue;
            }

            $normalized[] = (int) $service;
        }

        $normalized = array_values(array_unique(array_filter(
            $normalized,
            fn (int $service) => $service > 0
        )));

        sort($normalized);

        return $normalized;
    }
}
