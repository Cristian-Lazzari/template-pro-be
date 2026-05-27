<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class BookingsWithoutOrdersTrigger implements TriggerContract
{
    public function applyToQuery(Builder $query, array $params): void
    {
        $minBookings = max(1, (int) ($params['min_bookings'] ?? 1));

        $query
            ->where('reservations_count', '>=', $minBookings)
            ->where(function (Builder $q) {
                $q->whereNull('orders_count')
                  ->orWhere('orders_count', '=', 0);
            });
    }

    public function validationRules(): array
    {
        return [
            'min_bookings' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function defaultMetadata(): array
    {
        return ['min_bookings' => 1];
    }

    public function label(): string
    {
        return 'Prenotazioni senza ordini';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti che hanno effettuato almeno X prenotazioni ma non hanno mai ordinato online.';
    }

    public function requiredCustomerColumns(): array
    {
        return ['reservations_count', 'orders_count'];
    }

    public function getFailureReason(array $params, array $availableColumns): ?string
    {
        if (! array_key_exists('reservations_count', $availableColumns)) {
            return 'Colonna reservations_count non disponibile nella tabella customers.';
        }

        if (! array_key_exists('orders_count', $availableColumns)) {
            return 'Colonna orders_count non disponibile nella tabella customers.';
        }

        $minBookings = $params['min_bookings'] ?? null;

        if ($minBookings === null || (int) $minBookings < 1) {
            return 'Il parametro "min_bookings" deve essere un intero >= 1.';
        }

        return null;
    }
}
