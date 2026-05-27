<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class ValuableCustomerAtRiskTrigger implements TriggerContract
{
    private const ALLOWED_VALUE_TYPES = ['total_spent', 'orders_count', 'bookings_count', 'customer_score'];

    public function applyToQuery(Builder $query, array $params): void
    {
        $valueType    = $this->resolveValueType($params);
        $valueThreshold = max(0, (float) ($params['value_threshold'] ?? 0));
        $inactiveDays = max(1, (int) ($params['inactive_days'] ?? 60));

        // bookings_count maps to reservations_count in the customers table.
        $column = $valueType === 'bookings_count' ? 'reservations_count' : $valueType;

        $query
            ->where($column, '>=', $valueThreshold)
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<', now()->subDays($inactiveDays));
    }

    public function validationRules(): array
    {
        return [
            'value_type'      => ['required', 'string', 'in:total_spent,orders_count,bookings_count,customer_score'],
            'value_threshold' => ['required', 'numeric', 'min:0'],
            'inactive_days'   => ['required', 'integer', 'min:1', 'max:730'],
        ];
    }

    public function defaultMetadata(): array
    {
        return [
            'value_type'      => 'total_spent',
            'value_threshold' => 50,
            'inactive_days'   => 60,
        ];
    }

    public function label(): string
    {
        return 'Cliente di valore a rischio abbandono';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti che superano una soglia di valore ma sono inattivi da almeno X giorni.';
    }

    public function requiredCustomerColumns(): array
    {
        return ['last_activity_at'];
    }

    public function getFailureReason(array $params, array $availableColumns): ?string
    {
        if (! array_key_exists('last_activity_at', $availableColumns)) {
            return 'Colonna last_activity_at non disponibile nella tabella customers.';
        }

        $valueType = $this->resolveValueType($params);
        $column = $valueType === 'bookings_count' ? 'reservations_count' : $valueType;

        if (! array_key_exists($column, $availableColumns)) {
            return "Colonna {$column} non disponibile nella tabella customers.";
        }

        if (! in_array($valueType, self::ALLOWED_VALUE_TYPES, true)) {
            return 'Il parametro "value_type" deve essere uno tra: ' . implode(', ', self::ALLOWED_VALUE_TYPES) . '.';
        }

        $inactiveDays = $params['inactive_days'] ?? null;

        if ($inactiveDays === null || (int) $inactiveDays < 1) {
            return 'Il parametro "inactive_days" deve essere un intero >= 1.';
        }

        return null;
    }

    private function resolveValueType(array $params): string
    {
        $type = $params['value_type'] ?? 'total_spent';

        return in_array($type, self::ALLOWED_VALUE_TYPES, true) ? $type : 'total_spent';
    }
}
