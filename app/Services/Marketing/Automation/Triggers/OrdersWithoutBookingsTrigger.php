<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class OrdersWithoutBookingsTrigger implements TriggerContract
{
    public function applyToQuery(Builder $query, array $params): void
    {
        $minOrders = max(1, (int) ($params['min_orders'] ?? 1));

        $query
            ->where('orders_count', '>=', $minOrders)
            ->where(function (Builder $q) {
                $q->whereNull('reservations_count')
                  ->orWhere('reservations_count', '=', 0);
            });
    }

    public function validationRules(): array
    {
        return [
            'min_orders' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function defaultMetadata(): array
    {
        return ['min_orders' => 1];
    }

    public function label(): string
    {
        return 'Ordini senza prenotazioni';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti che hanno effettuato almeno X ordini ma non hanno mai prenotato un tavolo.';
    }

    public function requiredCustomerColumns(): array
    {
        return ['orders_count', 'reservations_count'];
    }

    public function getFailureReason(array $params, array $availableColumns): ?string
    {
        if (! array_key_exists('orders_count', $availableColumns)) {
            return 'Colonna orders_count non disponibile nella tabella customers.';
        }

        if (! array_key_exists('reservations_count', $availableColumns)) {
            return 'Colonna reservations_count non disponibile nella tabella customers.';
        }

        $minOrders = $params['min_orders'] ?? null;

        if ($minOrders === null || (int) $minOrders < 1) {
            return 'Il parametro "min_orders" deve essere un intero >= 1.';
        }

        return null;
    }
}
