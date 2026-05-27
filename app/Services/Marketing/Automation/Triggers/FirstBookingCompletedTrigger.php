<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class FirstBookingCompletedTrigger implements TriggerContract
{
    public function applyToQuery(Builder $query, array $params): void
    {
        $delayDays = max(1, (int) ($params['delay_days'] ?? 30));

        $query
            ->whereNotNull('first_booking_at')
            ->where('first_booking_at', '>=', now()->subDays($delayDays));
    }

    public function validationRules(): array
    {
        return [
            'delay_days' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function defaultMetadata(): array
    {
        return ['delay_days' => 30];
    }

    public function label(): string
    {
        return 'Dopo la prima prenotazione';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti che hanno effettuato la loro prima prenotazione negli ultimi X giorni. Il cooldown impedisce invii multipli.';
    }

    public function requiredCustomerColumns(): array
    {
        return ['first_booking_at'];
    }

    public function getFailureReason(array $params, array $availableColumns): ?string
    {
        if (! array_key_exists('first_booking_at', $availableColumns)) {
            return 'Colonna first_booking_at non disponibile. Esegui le migration.';
        }

        $delayDays = $params['delay_days'] ?? null;

        if ($delayDays === null || (int) $delayDays < 1) {
            return 'Il parametro "delay_days" deve essere un intero >= 1.';
        }

        return null;
    }
}
