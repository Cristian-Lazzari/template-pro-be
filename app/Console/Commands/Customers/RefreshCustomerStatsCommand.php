<?php

namespace App\Console\Commands\Customers;

use App\Models\Customer;
use App\Services\Customers\CustomerStatsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefreshCustomerStatsCommand extends Command
{
    protected $signature = 'customers:refresh-stats
        {--customer_id= : Processa solo il customer con questo ID}
        {--limit=500    : Numero massimo di customer da processare in questo run}
        {--dry-run      : Mostra cosa cambierebbe senza scrivere nulla}';

    protected $description = 'Ricalcola e persiste le statistiche denormalizzate (orders_count, total_spent, ecc.) per tutti i customer o per un singolo.';

    // Allineati con CustomerSegmentService — non duplicare qui i valori se cambiano nel futuro.
    private const CONFIRMED_ORDER_STATUSES       = [1, 2, 3, 5];
    private const CONFIRMED_RESERVATION_STATUSES = [1, 2, 3, 5];

    public function handle(CustomerStatsService $statsService): int
    {
        $customerId = $this->option('customer_id');
        $limit      = max(1, (int) $this->option('limit'));
        $dryRun     = (bool) $this->option('dry-run');

        $counters = [
            'processed' => 0,
            'changed'   => 0,
            'errors'    => 0,
        ];

        $query = Customer::query()->orderBy('id');

        if ($customerId !== null) {
            $query->where('id', (int) $customerId);
        }

        // chunk() di Eloquent ignora limit() sulla query base: gestiamo il limite manualmente
        // controllando il contatore processed dentro il callback.
        $query->chunk(50, function ($customers) use ($statsService, $dryRun, $limit, $customerId, &$counters) {
            foreach ($customers as $customer) {
                // Rispetta --limit solo quando non si processa un singolo customer specifico
                if ($customerId === null && $counters['processed'] >= $limit) {
                    return false; // interrompe il chunk
                }

                $counters['processed']++;

                try {
                    $before    = $this->snapshot($customer);
                    $projected = $this->project($customer);

                    if ($before !== $projected) {
                        $counters['changed']++;

                        if ($dryRun) {
                            $this->line('[dry-run] Customer #' . $customer->id . ' cambierebbe:');
                            foreach ($projected as $field => $value) {
                                if ($before[$field] !== $value) {
                                    $this->line("  {$field}: " . $this->display($before[$field]) . ' -> ' . $this->display($value));
                                }
                            }
                        }
                    }

                    if (! $dryRun) {
                        $statsService->refresh($customer);
                    }
                } catch (Throwable $e) {
                    $counters['errors']++;

                    Log::error('(RefreshCustomerStatsCommand) Errore su customer', [
                        'customer_id' => $customer->id,
                        'error'       => $e->getMessage(),
                    ]);

                    $this->error('Errore su customer #' . $customer->id . ': ' . $e->getMessage());
                }
            }
        });

        $output = json_encode(array_merge($counters, ['dry_run' => $dryRun]), JSON_PRETTY_PRINT);
        $this->line($output);

        return self::SUCCESS;
    }

    /**
     * Snapshot dei campi stats attuali sul modello (prima del refresh).
     *
     * @return array<string, mixed>
     */
    private function snapshot(Customer $customer): array
    {
        return [
            'orders_count'        => (int) ($customer->orders_count ?? 0),
            'total_spent'         => round((float) ($customer->total_spent ?? 0), 2),
            'average_order_value' => $customer->average_order_value !== null
                ? round((float) $customer->average_order_value, 2)
                : null,
            'first_order_at'      => $customer->first_order_at?->toDateTimeString(),
            'last_order_at'       => $customer->last_order_at?->toDateTimeString(),
            'reservations_count'  => (int) ($customer->reservations_count ?? 0),
            'first_booking_at'    => $customer->first_booking_at?->toDateTimeString(),
            'last_booking_at'     => $customer->last_booking_at?->toDateTimeString(),
            'last_activity_at'    => $customer->last_activity_at?->toDateTimeString(),
        ];
    }

    /**
     * Calcola i valori attesi (identico alla logica del service) senza scrivere sul DB.
     * Usato per confronto dry-run.
     *
     * @return array<string, mixed>
     */
    private function project(Customer $customer): array
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

        $candidates = array_filter([
            $lastOrderAt,
            $lastBookingAt,
            $customer->last_activity_at?->toDateTimeString(),
        ]);

        $lastActivityAt = $candidates !== []
            ? date('Y-m-d H:i:s', max(array_map('strtotime', $candidates)))
            : null;

        return [
            'orders_count'        => $ordersCount,
            'total_spent'         => $totalSpent,
            'average_order_value' => $averageOrderValue,
            'first_order_at'      => $firstOrderAt,
            'last_order_at'       => $lastOrderAt,
            'reservations_count'  => $reservationsCount,
            'first_booking_at'    => $firstBookingAt,
            'last_booking_at'     => $lastBookingAt,
            'last_activity_at'    => $lastActivityAt,
        ];
    }

    private function display(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        return (string) $value;
    }
}
