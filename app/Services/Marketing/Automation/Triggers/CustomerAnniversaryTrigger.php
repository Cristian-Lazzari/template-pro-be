<?php

namespace App\Services\Marketing\Automation\Triggers;

use App\Services\Marketing\Automation\TriggerContract;
use Illuminate\Database\Eloquent\Builder;

class CustomerAnniversaryTrigger implements TriggerContract
{
    private const ALLOWED_SOURCES = ['first_order', 'first_booking'];

    public function applyToQuery(Builder $query, array $params): void
    {
        $source     = $this->resolveSource($params);
        $daysBefore = max(0, min(30, (int) ($params['days_before'] ?? 0)));
        $column     = $source === 'first_booking' ? 'first_booking_at' : 'first_order_at';
        $today      = now()->startOfDay();

        // Build (MONTH, DAY) pairs covering today through today+daysBefore.
        // Column names come from our controlled constant — no injection risk.
        $conditions = [];
        for ($i = 0; $i <= $daysBefore; $i++) {
            $date = $today->copy()->addDays($i);
            $conditions[] = sprintf(
                '(MONTH(%s) = %d AND DAY(%s) = %d)',
                $column, $date->month,
                $column, $date->day
            );
        }

        $query
            ->whereNotNull($column)
            ->whereRaw('(' . implode(' OR ', $conditions) . ')');
    }

    public function validationRules(): array
    {
        return [
            'anniversary_source' => ['required', 'string', 'in:first_order,first_booking'],
            'days_before'        => ['required', 'integer', 'min:0', 'max:30'],
        ];
    }

    public function defaultMetadata(): array
    {
        return [
            'anniversary_source' => 'first_order',
            'days_before'        => 0,
        ];
    }

    public function label(): string
    {
        return 'Anniversario primo ordine/prenotazione';
    }

    public function description(): string
    {
        return 'Raggiungi i clienti nel giorno dell\'anniversario del loro primo ordine o prima prenotazione, o fino a X giorni prima.';
    }

    public function requiredCustomerColumns(): array
    {
        return ['first_order_at', 'first_booking_at'];
    }

    public function getFailureReason(array $params, array $availableColumns): ?string
    {
        $source = $this->resolveSource($params);
        $column = $source === 'first_booking' ? 'first_booking_at' : 'first_order_at';

        if (! array_key_exists($column, $availableColumns)) {
            return "Colonna {$column} non disponibile. Esegui le migration.";
        }

        if (! in_array($source, self::ALLOWED_SOURCES, true)) {
            return 'Il parametro "anniversary_source" deve essere "first_order" o "first_booking".';
        }

        $daysBefore = $params['days_before'] ?? null;

        if ($daysBefore === null || (int) $daysBefore < 0) {
            return 'Il parametro "days_before" deve essere un intero >= 0.';
        }

        return null;
    }

    private function resolveSource(array $params): string
    {
        $source = $params['anniversary_source'] ?? 'first_order';

        return in_array($source, self::ALLOWED_SOURCES, true) ? $source : 'first_order';
    }
}
