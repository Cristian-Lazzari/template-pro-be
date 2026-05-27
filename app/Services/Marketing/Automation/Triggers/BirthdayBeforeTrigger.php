<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\ProfilingConsentTriggerContract;
use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class BirthdayBeforeTrigger implements TriggerContract, ProfilingConsentTriggerContract
{
    public function applyToQuery(Builder $query, array $params): void
    {
        $daysBefore = max(0, min(30, (int) ($params['days_before'] ?? 0)));
        $today = now()->startOfDay();

        // Build a list of (month, day) pairs covering today through today + days_before.
        // This handles year-crossing correctly without relying on DAYOFYEAR (which breaks on leap years).
        $conditions = [];
        for ($i = 0; $i <= $daysBefore; $i++) {
            $date = $today->copy()->addDays($i);
            $conditions[] = sprintf(
                '(MONTH(birthday) = %d AND DAY(birthday) = %d)',
                $date->month,
                $date->day
            );
        }

        $query
            ->whereNotNull('birthday')
            ->whereRaw('(' . implode(' OR ', $conditions) . ')');
    }

    public function validationRules(): array
    {
        return [
            'days_before' => ['required', 'integer', 'min:0', 'max:30'],
        ];
    }

    public function defaultMetadata(): array
    {
        return ['days_before' => 0];
    }

    public function label(): string
    {
        return 'X giorni prima del compleanno';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti il giorno del compleanno o fino a X giorni prima. Richiede che il cliente abbia fornito la data di nascita.';
    }

    public function requiredCustomerColumns(): array
    {
        return ['birthday'];
    }

    public function getFailureReason(array $params, array $availableColumns): ?string
    {
        $birthdayColumn = null;
        foreach (['birthday', 'birth_date', 'date_of_birth', 'dob'] as $candidate) {
            if (array_key_exists($candidate, $availableColumns)) {
                $birthdayColumn = $candidate;
                break;
            }
        }

        if ($birthdayColumn === null) {
            return 'Nessuna colonna data di nascita disponibile nella tabella customers.';
        }

        $daysBefore = $params['days_before'] ?? null;

        if ($daysBefore === null || (int) $daysBefore < 0) {
            return 'Il parametro "days_before" deve essere un intero >= 0.';
        }

        return null;
    }

    /**
     * Questo trigger usa la data di nascita a fini promozionali.
     * Richiede che il cliente abbia espresso consenso alla profilazione (tracking_consent_at).
     */
    public function requiresProfilingConsent(): bool
    {
        return true;
    }
}
