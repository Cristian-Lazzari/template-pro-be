<?php

namespace App\Services\Customers;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerStatsService
{
    // Status ordini confermati: allineato con CustomerSegmentService::CONFIRMED_ORDER_STATUSES = [1, 2, 3, 5].
    // Status 0 = cancellato, 4 = in attesa di pagamento (pending Stripe), 6 = rimborsato.
    // Si escludono 0, 4 e 6 perché non rappresentano valore economico confermato.
    private const CONFIRMED_ORDER_STATUSES = [1, 2, 3, 5];

    // Status prenotazioni confermate: allineato con CustomerSegmentService::CONFIRMED_RESERVATION_STATUSES = [1, 2, 3, 5].
    // Status 0 = cancellata. Includere 2 (in attesa conferma admin) è coerente col resto del CRM.
    private const CONFIRMED_RESERVATION_STATUSES = [1, 2, 3, 5];

    /**
     * Ricalcola e persiste tutte le statistiche denormalizzate per un singolo customer.
     *
     * Usa una sola query aggregata per gli ordini e una per le prenotazioni.
     * Idempotente: può essere chiamato N volte senza effetti collaterali.
     */
    public function refresh(Customer $customer): void
    {
        $customerId = $customer->getKey();

        $orderStats = DB::table('orders')
            ->selectRaw('
                COUNT(*) as orders_count,
                COALESCE(SUM(tot_price), 0) as total_spent,
                MIN(created_at) as first_order_at,
                MAX(created_at) as last_order_at
            ')
            ->where('customer_id', $customerId)
            ->whereIn('status', self::CONFIRMED_ORDER_STATUSES)
            ->first();

        $reservationStats = DB::table('reservations')
            ->selectRaw('
                COUNT(*) as reservations_count,
                MIN(created_at) as first_booking_at,
                MAX(created_at) as last_booking_at
            ')
            ->where('customer_id', $customerId)
            ->whereIn('status', self::CONFIRMED_RESERVATION_STATUSES)
            ->first();

        $ordersCount  = (int) ($orderStats->orders_count ?? 0);
        $totalSpent   = round((float) ($orderStats->total_spent ?? 0), 2);
        $firstOrderAt = $orderStats->first_order_at ?? null;
        $lastOrderAt  = $orderStats->last_order_at ?? null;

        $reservationsCount = (int) ($reservationStats->reservations_count ?? 0);
        $firstBookingAt    = $reservationStats->first_booking_at ?? null;
        $lastBookingAt     = $reservationStats->last_booking_at ?? null;

        $averageOrderValue = $ordersCount > 0
            ? round($totalSpent / $ordersCount, 2)
            : null;

        // last_activity_at = il più recente tra ultimo ordine, ultima prenotazione
        // e i valori già presenti su profiling/email che il CRM potrebbe avere aggiornato
        // (non sovrascriviamo se la data existente è più recente di entrambe le attività).
        $lastActivityAt = $this->resolveLastActivityAt(
            $lastOrderAt,
            $lastBookingAt,
            $customer->last_activity_at?->toDateTimeString()
        );

        $customer->orders_count        = $ordersCount;
        $customer->total_spent         = $totalSpent;
        $customer->average_order_value = $averageOrderValue;
        $customer->first_order_at      = $firstOrderAt;
        $customer->last_order_at       = $lastOrderAt;

        $customer->reservations_count  = $reservationsCount;
        $customer->first_booking_at    = $firstBookingAt;
        $customer->last_booking_at     = $lastBookingAt;

        $customer->last_activity_at    = $lastActivityAt;

        $customer->save();
    }

    /**
     * Restituisce il timestamp più recente tra ultimo ordine, ultima prenotazione
     * e il valore esistente su last_activity_at (che potrebbe essere aggiornato
     * da tracciamenti email/marketing non riflessi in ordini o prenotazioni).
     */
    private function resolveLastActivityAt(
        ?string $lastOrderAt,
        ?string $lastBookingAt,
        ?string $existingLastActivityAt
    ): ?string {
        $candidates = array_filter([$lastOrderAt, $lastBookingAt, $existingLastActivityAt]);

        if ($candidates === []) {
            return null;
        }

        // strtotime funziona correttamente per date MySQL nel formato Y-m-d H:i:s
        $timestamps = array_map('strtotime', $candidates);
        $maxTimestamp = max($timestamps);

        return date('Y-m-d H:i:s', $maxTimestamp);
    }
}
