<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class FirstOrderCompletedTrigger implements TriggerContract
{
    public function applyToQuery(Builder $query, array $params): void
    {
        // delay_days defines the "catch-up window": match customers whose first order
        // was placed within the last N days. Default 30 gives enough runway so the
        // twice-daily runner doesn't miss new customers. Cooldown in AutomationAssignmentService
        // guarantees each customer is contacted only once per automation lifecycle.
        $delayDays = max(1, (int) ($params['delay_days'] ?? 30));

        $query
            ->whereNotNull('first_order_at')
            ->where('first_order_at', '>=', now()->subDays($delayDays));
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
        return 'Dopo il primo ordine';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti che hanno completato il loro primo ordine negli ultimi X giorni. Il cooldown impedisce invii multipli.';
    }

    public function requiredCustomerColumns(): array
    {
        return ['first_order_at'];
    }

    public function getFailureReason(array $params, array $availableColumns): ?string
    {
        if (! array_key_exists('first_order_at', $availableColumns)) {
            return 'Colonna first_order_at non disponibile. Esegui le migration.';
        }

        $delayDays = $params['delay_days'] ?? null;

        if ($delayDays === null || (int) $delayDays < 1) {
            return 'Il parametro "delay_days" deve essere un intero >= 1.';
        }

        return null;
    }
}
