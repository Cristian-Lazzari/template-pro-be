<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class NoOrderSinceTrigger implements TriggerContract
{
    public function applyToQuery(Builder $query, array $params): void
    {
        $days = max(1, (int) ($params['days'] ?? 30));

        $query
            ->whereNotNull('last_order_at')
            ->where('last_order_at', '<', now()->subDays($days));
    }

    public function validationRules(): array
    {
        return [
            'days' => ['required', 'integer', 'min:1', 'max:730'],
        ];
    }

    public function defaultMetadata(): array
    {
        return ['days' => 30];
    }

    public function label(): string
    {
        return 'Nessun ordine da X giorni';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti che non hanno effettuato un ordine da almeno X giorni.';
    }

    public function requiredCustomerColumns(): array
    {
        return ['last_order_at'];
    }

    public function getFailureReason(array $params, array $availableColumns): ?string
    {
        if (! array_key_exists('last_order_at', $availableColumns)) {
            return 'Colonna last_order_at non disponibile. Esegui le migration.';
        }

        $days = $params['days'] ?? null;

        if ($days === null || (int) $days < 1) {
            return 'Il parametro "days" deve essere un intero >= 1.';
        }

        return null;
    }
}
