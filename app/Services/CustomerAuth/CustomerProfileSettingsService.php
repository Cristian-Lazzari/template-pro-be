<?php

namespace App\Services\CustomerAuth;

use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Support\Str;

class CustomerProfileSettingsService
{
    private const SETTING_NAME = 'customer_profile';

    public function get(): array
    {
        $setting = Setting::query()->firstOrCreate(
            ['name' => self::SETTING_NAME],
            [
                'status' => 1,
                'property' => json_encode($this->defaults()),
            ]
        );

        $property = json_decode($setting->property ?? '[]', true);
        if (!is_array($property)) {
            $property = [];
        }

        return array_replace_recursive($this->defaults(), $property);
    }

    public function update(array $input): array
    {
        $payload = [
            'marketing_consent_text' => $this->sanitizeText(
                $input['marketing_consent_text'] ?? $this->defaults()['marketing_consent_text']
            ),
            'profiling_consent_text' => $this->sanitizeText(
                $input['profiling_consent_text'] ?? $this->defaults()['profiling_consent_text']
            ),
            'questions' => $this->normalizeQuestions($input['questions'] ?? []),
        ];

        Setting::query()->updateOrCreate(
            ['name' => self::SETTING_NAME],
            [
                'status' => 1,
                'property' => json_encode($payload),
            ]
        );

        return $payload;
    }

    public function normalizeAnswers(array $answers, array $questions): array
    {
        $normalized = [];

        foreach ($questions as $question) {
            $key = $question['key'] ?? null;
            if (!$key || !array_key_exists($key, $answers)) {
                continue;
            }

            $value = $answers[$key];
            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    public function isRegistrationComplete(Customer $customer, array $questions): bool
    {
        if (
            trim((string) $customer->name) === ''
            || trim((string) $customer->surname) === ''
            || trim((string) $customer->gender) === ''
            || !$customer->age
        ) {
            return false;
        }

        $answers = is_array($customer->profile_answers) ? $customer->profile_answers : [];

        foreach ($questions as $question) {
            if (!($question['required'] ?? false)) {
                continue;
            }

            $key = $question['key'] ?? null;
            if (!$key || !array_key_exists($key, $answers)) {
                return false;
            }

            $value = $answers[$key];
            if ($value === null || $value === '' || $value === []) {
                return false;
            }
        }

        return true;
    }

    public function defaults(): array
    {
        return [
            'marketing_consent_text' => 'Vuoi ricevere novita, eventi e offerte del ristorante via email?',
            'profiling_consent_text' => 'Vuoi ricevere promozioni personalizzate in base ai tuoi gusti e alle tue preferenze?',
            'questions' => [],
        ];
    }

    private function normalizeQuestions(array $questions): array
    {
        $normalized = [];
        $usedKeys = [];

        foreach ($questions as $index => $question) {
            if (!is_array($question)) {
                continue;
            }

            $label = trim((string) ($question['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $baseKey = trim((string) ($question['key'] ?? ''));
            $baseKey = $baseKey !== '' ? Str::slug($baseKey, '_') : Str::slug($label, '_');
            $baseKey = $baseKey !== '' ? $baseKey : 'question_' . ($index + 1);

            $key = $baseKey;
            $suffix = 2;
            while (in_array($key, $usedKeys, true)) {
                $key = $baseKey . '_' . $suffix;
                $suffix++;
            }

            $usedKeys[] = $key;

            $normalized[] = [
                'key' => $key,
                'label' => $label,
                'placeholder' => $this->sanitizeText($question['placeholder'] ?? ''),
                'required' => filter_var($question['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return array_values($normalized);
    }

    private function sanitizeText($value): string
    {
        return trim((string) $value);
    }
}
