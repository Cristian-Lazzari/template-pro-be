<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class HighAverageOrderValueTrigger implements TriggerContract
{
    public function applyToQuery(Builder $query, array $params): void
    {
        $threshold  = max(0, (float) ($params['average_order_value'] ?? 0));
        $minOrders  = isset($params['min_orders']) ? max(1, (int) $params['min_orders']) : null;

        $query
            ->whereNotNull('average_order_value')
            ->where('average_order_value', '>=', $threshold);

        if ($minOrders !== null) {
            $query->where('orders_count', '>=', $minOrders);
        }
    }

    public function validationRules(): array
    {
        return [
            'average_order_value' => ['required', 'numeric', 'min:0'],
            'min_orders'          => ['nullable', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function defaultMetadata(): array
    {
        return [
            'average_order_value' => 30,
            'min_orders'          => null,
        ];
    }

    public function label(): string
    {
        return 'Carrello medio elevato';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti con un valore medio per ordine superiore a X euro. Opzionalmente richiede un numero minimo di ordini.';
    }

    public function requiredCustomerColumns(): array
    {
        return ['average_order_value'];
    }

    public function getFailureReason(array $params, array $availableColumns): ?string
    {
        if (! array_key_exists('average_order_value', $availableColumns)) {
            return 'Colonna average_order_value non disponibile. Esegui le migration.';
        }

        $threshold = $params['average_order_value'] ?? null;

        if ($threshold === null || (float) $threshold < 0) {
            return 'Il parametro "average_order_value" deve essere un numero >= 0.';
        }

        return null;
    }
}
