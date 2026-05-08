<?php

namespace App\Services\CustomerAuth;

use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Support\Str;

class CustomerProfileSettingsService
{
    private const SETTING_NAME = 'customer_profile';

    private const STATIC_CONSENT_TEXTS = [
        'marketing_consent_text' => 'Acconsento a ricevere via email novità, offerte e promozioni del ristorante.',
        'profiling_consent_text' => 'Acconsento all\'uso delle mie preferenze, risposte al questionario e storico ordini/prenotazioni per ricevere offerte e comunicazioni personalizzate.',
        'email_marketing_label' => 'Acconsento a ricevere via email novità, offerte e promozioni del ristorante.',
        'whatsapp_marketing_label' => 'Acconsento a ricevere via WhatsApp comunicazioni promozionali, offerte e novità del ristorante.',
        'profiling_label' => 'Acconsento all\'uso delle mie preferenze, risposte al questionario e storico ordini/prenotazioni per ricevere offerte e comunicazioni personalizzate.',
        'profiling_note' => 'Le informazioni restano solo a questo ristorante e vengono usate per proporti offerte più pertinenti.',
        'tracking_label' => 'Acconsento al tracciamento delle interazioni con le comunicazioni marketing, come aperture e click, per misurarne l\'efficacia.',
        'privacy_label' => 'Ho letto l\'informativa privacy e acconsento al trattamento dei dati necessari per gestire il servizio richiesto.',
        'accept_all_label' => 'Accetta tutti i consensi facoltativi',
        'cookie_text' => 'Usiamo cookie tecnici necessari e, solo con il tuo consenso, strumenti di tracciamento e analisi per migliorare l\'esperienza.',
    ];

    public function get(): array
    {
        $setting = $this->setting();

        return $this->withStaticConsentTexts(
            $this->decodeProperty($setting->property)
        );
    }

    public function staticConsentTexts(): array
    {
        return self::STATIC_CONSENT_TEXTS;
    }

    public function update(array $input): array
    {
        $setting = $this->setting();
        $property = $this->decodeProperty($setting->property);

        $questions = $input['questions'] ?? $property['questions'] ?? [];
        $property['questions'] = $this->normalizeQuestions(is_array($questions) ? $questions : []);

        $setting->status = 1;
        $setting->property = json_encode($property);
        $setting->save();

        return $this->withStaticConsentTexts($property);
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
        return array_replace(self::STATIC_CONSENT_TEXTS, [
            'questions' => [],
        ]);
    }

    private function setting(): Setting
    {
        return Setting::query()->firstOrCreate(
            ['name' => self::SETTING_NAME],
            [
                'status' => 1,
                'property' => json_encode(['questions' => []]),
            ]
        );
    }

    private function decodeProperty(?string $property): array
    {
        $decoded = json_decode($property ?? '[]', true);

        return is_array($decoded) ? $decoded : [];
    }

    private function withStaticConsentTexts(array $property): array
    {
        $questions = $property['questions'] ?? [];

        return array_replace_recursive(
            $property,
            self::STATIC_CONSENT_TEXTS,
            ['questions' => $this->normalizeQuestions(is_array($questions) ? $questions : [])]
        );
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

            $normalizedQuestion = [
                'key' => $key,
                'label' => $label,
                'placeholder' => $this->sanitizeText($question['placeholder'] ?? ''),
                'required' => filter_var($question['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];

            if (isset($question['options']) && is_array($question['options'])) {
                $normalizedQuestion['options'] = array_values($question['options']);
            }

            $normalized[] = $normalizedQuestion;
        }

        return array_values($normalized);
    }

    private function sanitizeText($value): string
    {
        return trim((string) $value);
    }
}
