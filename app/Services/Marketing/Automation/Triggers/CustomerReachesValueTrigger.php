<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class CustomerReachesValueTrigger implements TriggerContract
{
    private const ALLOWED_TYPES = ['total_spent', 'orders_count', 'bookings_count'];

    public function applyToQuery(Builder $query, array $params): void
    {
        $type = $this->resolveType($params);
        $value = max(0, (float) ($params['threshold_value'] ?? 0));

        // bookings_count is stored as reservations_count in the customers table.
        $column = $type === 'bookings_count' ? 'reservations_count' : $type;

        $query->where($column, '>=', $value);
    }

    public function validationRules(): array
    {
        return [
            'threshold_type'  => ['required', 'string', 'in:total_spent,orders_count,bookings_count'],
            'threshold_value' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function defaultMetadata(): array
    {
        return [
            'threshold_type'  => 'total_spent',
            'threshold_value' => 100,
        ];
    }

    public function label(): string
    {
        return 'Cliente raggiunge una soglia di valore';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti quando superano una soglia di spesa totale, numero di ordini o numero di prenotazioni.';
    }

    public function requiredCustomerColumns(): array
    {
        return ['total_spent', 'orders_count', 'reservations_count'];
    }

    public function getFailureReason(array $params, array $availableColumns): ?string
    {
        $type = $this->resolveType($params);
        $column = $type === 'bookings_count' ? 'reservations_count' : $type;

        if (! array_key_exists($column, $availableColumns)) {
            return "Colonna {$column} non disponibile nella tabella customers.";
        }

        if (! in_array($type, self::ALLOWED_TYPES, true)) {
            return 'Il parametro "threshold_type" deve essere uno tra: ' . implode(', ', self::ALLOWED_TYPES) . '.';
        }

        $value = $params['threshold_value'] ?? null;

        if ($value === null || (float) $value < 0) {
            return 'Il parametro "threshold_value" deve essere un numero >= 0.';
        }

        return null;
    }

    private function resolveType(array $params): string
    {
        $type = $params['threshold_type'] ?? 'total_spent';

        return in_array($type, self::ALLOWED_TYPES, true) ? $type : 'total_spent';
    }
}
