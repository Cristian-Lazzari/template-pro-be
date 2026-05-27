<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class NoInteractionSinceTrigger implements TriggerContract
{
    public function applyToQuery(Builder $query, array $params): void
    {
        $days = max(1, (int) ($params['days'] ?? 30));

        $query
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<', now()->subDays($days));
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
        return 'Nessuna interazione da X giorni';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti che non interagiscono con il ristorante da almeno X giorni.';
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

        $days = $params['days'] ?? null;

        if ($days === null || (int) $days < 1) {
            return 'Il parametro "days" deve essere un intero >= 1.';
        }

        return null;
    }
}
