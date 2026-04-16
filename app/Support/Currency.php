<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class Currency
{
    protected static ?array $resolved = null;
    protected static ?string $scopeKey = null;

    public static function supported(): array
    {
        return [
            'EUR' => [
                'code' => 'EUR',
                'symbol' => '€',
                'label' => 'Euro',
                'decimals' => 2,
            ],
            'USD' => [
                'code' => 'USD',
                'symbol' => '$',
                'label' => 'US Dollar',
                'decimals' => 2,
            ],
            'GBP' => [
                'code' => 'GBP',
                'symbol' => '£',
                'label' => 'Sterlina',
                'decimals' => 2,
            ],
            'CHF' => [
                'code' => 'CHF',
                'symbol' => 'CHF',
                'label' => 'Franco svizzero',
                'decimals' => 2,
            ],
            'CAD' => [
                'code' => 'CAD',
                'symbol' => 'C$',
                'label' => 'Canadian Dollar',
                'decimals' => 2,
            ],
            'AUD' => [
                'code' => 'AUD',
                'symbol' => 'A$',
                'label' => 'Australian Dollar',
                'decimals' => 2,
            ],
            'JPY' => [
                'code' => 'JPY',
                'symbol' => '¥',
                'label' => 'Yen giapponese',
                'decimals' => 0,
            ],
        ];
    }

    public static function defaultDefinition(): array
    {
        return self::supported()['EUR'];
    }

    public static function normalize(?string $code): array
    {
        $code = strtoupper(trim((string) $code));

        return self::supported()[$code] ?? self::defaultDefinition();
    }

    public static function definition(): array
    {
        $scopeKey = self::resolveScopeKey();

        if (self::$resolved !== null && self::$scopeKey === $scopeKey) {
            return self::$resolved;
        }

        self::$scopeKey = $scopeKey;
        $fallback = self::defaultDefinition();

        try {
            if (!Schema::hasTable('settings')) {
                return self::$resolved = $fallback;
            }

            $setting = Setting::query()->where('name', 'Valuta')->first();
        } catch (\Throwable $exception) {
            return self::$resolved = $fallback;
        }

        if (!$setting) {
            return self::$resolved = $fallback;
        }

        $property = json_decode($setting->property ?? '{}', true);

        if (!is_array($property)) {
            return self::$resolved = $fallback;
        }

        $code = strtoupper(trim((string) ($property['code'] ?? '')));

        if ($code === '') {
            return self::$resolved = $fallback;
        }

        $supported = self::supported()[$code] ?? null;

        return self::$resolved = [
            'code' => $code,
            'symbol' => trim((string) ($property['symbol'] ?? $supported['symbol'] ?? $fallback['symbol'])),
            'label' => trim((string) ($property['label'] ?? $supported['label'] ?? $fallback['label'])),
            'decimals' => self::normalizeDecimals($property['decimals'] ?? $supported['decimals'] ?? $fallback['decimals']),
        ];
    }

    public static function code(): string
    {
        return self::definition()['code'];
    }

    public static function symbol(): string
    {
        return self::definition()['symbol'];
    }

    public static function stripeCode(): string
    {
        return strtolower(self::code());
    }

    public static function decimals(): int
    {
        return self::normalizeDecimals(self::definition()['decimals'] ?? 2);
    }

    public static function inputStep(): string
    {
        $decimals = self::decimals();

        if ($decimals <= 0) {
            return '1';
        }

        return '0.' . str_repeat('0', $decimals - 1) . '1';
    }

    public static function formatForInput($value): string
    {
        return number_format(self::roundAmount($value), self::decimals(), '.', '');
    }

    public static function parseInput($value): float
    {
        return self::roundAmount(self::parseNumeric($value));
    }

    public static function roundAmount($value): float
    {
        return round((float) ($value ?? 0), self::decimals());
    }

    public static function minorUnitMultiplier(?int $decimals = null): int
    {
        $decimals ??= self::decimals();

        return 10 ** max(0, $decimals);
    }

    public static function toMinorUnits($value): int
    {
        return (int) round(self::roundAmount($value) * self::minorUnitMultiplier());
    }

    public static function fromMinorUnits($value, ?int $decimals = null): float
    {
        $decimals ??= self::decimals();

        return round(((float) ($value ?? 0)) / self::minorUnitMultiplier($decimals), $decimals);
    }

    public static function formatCents($value, ?int $decimals = null): string
    {
        return self::formatAmount($value, $decimals);
    }

    public static function formatAmount($value, ?int $decimals = null): string
    {
        $decimals ??= self::decimals();

        return self::prefix() . number_format((float) ($value ?? 0), $decimals, ',', '.');
    }

    protected static function prefix(): string
    {
        $symbol = self::symbol();

        return mb_strlen($symbol) > 1 ? $symbol . ' ' : $symbol;
    }

    protected static function normalizeDecimals($value): int
    {
        $decimals = (int) $value;

        return $decimals >= 0 ? $decimals : 2;
    }

    protected static function parseNumeric($value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return 0.0;
        }

        $normalized = preg_replace('/\s+/', '', $normalized) ?? '';

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $lastComma = strrpos($normalized, ',');
            $lastDot = strrpos($normalized, '.');

            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } else {
            $normalized = str_replace(',', '.', $normalized);
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', $normalized) ?? '';

        if ($normalized === '' || $normalized === '-' || $normalized === '.' || $normalized === '-.') {
            return 0.0;
        }

        $parts = explode('.', $normalized);

        if (count($parts) > 2) {
            $normalized = array_shift($parts) . '.' . implode('', $parts);
        }

        return (float) $normalized;
    }

    protected static function resolveScopeKey(): string
    {
        try {
            if (app()->bound('request')) {
                return 'request:' . spl_object_id(app('request'));
            }
        } catch (\Throwable $exception) {
            // Fallback handled below.
        }

        return 'app:' . spl_object_id(app());
    }
}
