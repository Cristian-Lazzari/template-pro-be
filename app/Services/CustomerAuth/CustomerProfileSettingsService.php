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
        $current = $this->get();

        $payload = [
            'marketing_consent_text' => $this->sanitizeText(
                $input['marketing_consent_text'] ?? $current['marketing_consent_text']
            ),
            'profiling_consent_text' => $this->sanitizeText(
                $input['profiling_consent_text'] ?? $current['profiling_consent_text']
            ),
            'email_marketing_label' => $this->sanitizeText(
                $input['email_marketing_label'] ?? $current['email_marketing_label']
            ),
            'whatsapp_marketing_label' => $this->sanitizeText(
                $input['whatsapp_marketing_label'] ?? $current['whatsapp_marketing_label']
            ),
            'profiling_label' => $this->sanitizeText(
                $input['profiling_label'] ?? $current['profiling_label']
            ),
            'tracking_label' => $this->sanitizeText(
                $input['tracking_label'] ?? $current['tracking_label']
            ),
            'privacy_label' => $this->sanitizeText(
                $input['privacy_label'] ?? $current['privacy_label']
            ),
            'accept_all_label' => $this->sanitizeText(
                $input['accept_all_label'] ?? $current['accept_all_label']
            ),
            'questions' => $this->normalizeQuestions($input['questions'] ?? $current['questions'] ?? []),
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
            'email_marketing_label' => 'Voglio ricevere offerte e promozioni via email',
            'whatsapp_marketing_label' => 'Voglio ricevere offerte e promozioni via WhatsApp',
            'profiling_label' => 'Acconsento alla profilazione per ricevere offerte piu pertinenti',
            'tracking_label' => 'Acconsento al tracking delle interazioni marketing, come aperture e click',
            'privacy_label' => 'Accetto l\'informativa privacy e autorizzo il trattamento dei dati necessari per gestire ordine/prenotazione.',
            'accept_all_label' => 'Accetta tutti e ricevi promozioni e offerte personalizzate',
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
