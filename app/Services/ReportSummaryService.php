<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ReportSummaryService
{
    /**
     * Genera il payload del report per il backoffice centrale.
     *
     * Il metodo è costruito per essere difensivo: ogni blocco di metriche è
     * isolato in un try/catch e verifica l'esistenza delle tabelle prima di
     * fare query. Se una tabella manca (istanza parzialmente migrata o pack
     * diverso), la metrica viene marcata come non disponibile e viene aggiunto
     * un warning — l'endpoint non crasha mai.
     *
     * @param  Carbon  $from  Inizio del periodo (incluso, ora 00:00:00)
     * @param  Carbon  $to    Fine del periodo (incluso, ora 23:59:59)
     * @return array
     */
    public function generate(Carbon $from, Carbon $to): array
    {
        $warnings    = [];
        $revenueUnit = config('configurazione.private_report_revenue_unit', 'euros');

        $ordersPayload       = $this->buildOrdersPayload($from, $to, $revenueUnit, $warnings);
        $reservationsPayload = $this->buildReservationsPayload($from, $to, $warnings);

        return [
            'api_version'   => '2',
            'revenue_unit'  => $revenueUnit,
            'period'        => [
                'from' => $from->toDateString(),
                'to'   => $to->toDateString(),
            ],
            'instance'      => [
                'pack'   => config('configurazione.subscription') ?: null,
                'name'   => config('configurazione.APP_NAME') ?: config('app.name') ?: null,
                'domain' => config('configurazione.domain') ?: null,
            ],
            'orders'        => $ordersPayload,
            'reservations'  => $reservationsPayload,
            // ── V2 blocks ─────────────────────────────────────────────────────
            'periods'       => $this->buildPeriodsPayload($revenueUnit, $warnings),
            'daily'         => $this->buildDailyPayload($revenueUnit, $warnings),
            'usage'         => $this->buildUsagePayload($warnings),
            // ──────────────────────────────────────────────────────────────────
            'data_warnings' => $warnings,
            'generated_at'  => now()->toIso8601String(),
        ];
    }

    // =========================================================================
    // V1 — blocchi esistenti (invariati)
    // =========================================================================

    /**
     * Calcola le metriche degli ordini nel periodo.
     *
     * Gli ordini sono filtrati per created_at perché date_slot è una stringa
     * in formato "DD/MM/YYYY HH:MM" e non è indicizzata né affidabile per range.
     *
     * status = 1 => confermato (valore storico del progetto)
     * tot_price è decimal(12,2) dopo la migration 2026_04_16_110000
     */
    private function buildOrdersPayload(
        Carbon $from,
        Carbon $to,
        string $revenueUnit,
        array &$warnings
    ): array {
        try {
            if (! Schema::hasTable('orders')) {
                $warnings[] = 'orders_table_missing: tabella orders non trovata in questo database.';
                return ['total' => 0, 'revenue_confirmed' => null, 'available' => false];
            }

            $query = DB::table('orders')
                ->whereBetween('created_at', [
                    $from->copy()->startOfDay(),
                    $to->copy()->endOfDay(),
                ]);

            $total = (clone $query)->count();

            $revenueConfirmed = null;
            if (in_array($revenueUnit, ['euros', 'cents'], true)) {
                if (! Schema::hasColumn('orders', 'tot_price') || ! Schema::hasColumn('orders', 'status')) {
                    $warnings[] = 'orders_revenue_columns_missing: colonne tot_price o status non trovate, revenue non calcolabile.';
                } else {
                    $rawRevenue = (clone $query)
                        ->where('status', 1)
                        ->sum('tot_price');

                    $revenueConfirmed = $revenueUnit === 'cents'
                        ? (int) round($rawRevenue * 100)
                        : round((float) $rawRevenue, 2);
                }
            }

            return [
                'total'             => $total,
                'revenue_confirmed' => $revenueConfirmed,
                'available'         => true,
            ];
        } catch (\Throwable $e) {
            Log::error('ReportSummaryService: errore nel blocco orders', [
                'message' => $e->getMessage(),
                'from'    => $from->toDateString(),
                'to'      => $to->toDateString(),
            ]);
            $warnings[] = 'orders_error: impossibile calcolare le metriche degli ordini (' . $e->getMessage() . ').';
            return ['total' => 0, 'revenue_confirmed' => null, 'available' => false];
        }
    }

    /**
     * Calcola le metriche delle prenotazioni nel periodo.
     */
    private function buildReservationsPayload(
        Carbon $from,
        Carbon $to,
        array &$warnings
    ): array {
        try {
            if (! Schema::hasTable('reservations')) {
                $warnings[] = 'reservations_table_missing: tabella reservations non trovata in questo database.';
                return ['total' => 0, 'total_covers' => 0, 'available' => false];
            }

            $query = DB::table('reservations')
                ->whereBetween('created_at', [
                    $from->copy()->startOfDay(),
                    $to->copy()->endOfDay(),
                ]);

            $total      = (clone $query)->count();
            $totalCovers = $this->sumCovers($query, $warnings);

            return [
                'total'        => $total,
                'total_covers' => $totalCovers,
                'available'    => true,
            ];
        } catch (\Throwable $e) {
            Log::error('ReportSummaryService: errore nel blocco reservations', [
                'message' => $e->getMessage(),
                'from'    => $from->toDateString(),
                'to'      => $to->toDateString(),
            ]);
            $warnings[] = 'reservations_error: impossibile calcolare le metriche delle prenotazioni (' . $e->getMessage() . ').';
            return ['total' => 0, 'total_covers' => 0, 'available' => false];
        }
    }

    /**
     * Somma i coperti da n_person (stringa JSON: {"adult": N, "child": M}).
     */
    private function sumCovers(\Illuminate\Database\Query\Builder $baseQuery, array &$warnings): int
    {
        if (! Schema::hasColumn('reservations', 'n_person')) {
            $warnings[] = 'reservations_n_person_missing: colonna n_person non trovata, coperti non calcolabili.';
            return 0;
        }

        try {
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                $result = (clone $baseQuery)
                    ->selectRaw(
                        'SUM(
                            COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(n_person, "$.adult")) AS UNSIGNED), 0) +
                            COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(n_person, "$.child")) AS UNSIGNED), 0)
                        ) as total_covers'
                    )
                    ->first();

                return (int) ($result->total_covers ?? 0);
            }

            $rows = (clone $baseQuery)->select('n_person')->get();
            $sum  = 0;
            foreach ($rows as $row) {
                $parsed = json_decode($row->n_person ?? '{}', true);
                if (is_array($parsed)) {
                    $sum += (int) ($parsed['adult'] ?? 0);
                    $sum += (int) ($parsed['child'] ?? 0);
                }
            }
            return $sum;
        } catch (\Throwable $e) {
            Log::error('ReportSummaryService: errore nel calcolo coperti', ['message' => $e->getMessage()]);
            $warnings[] = 'reservations_covers_error: impossibile sommare i coperti (' . $e->getMessage() . ').';
            return 0;
        }
    }

    // =========================================================================
    // V2 — nuovi blocchi
    // =========================================================================

    /**
     * Calcola i 6 periodi fissi indipendenti dal range principale della richiesta.
     * Riusa buildOrdersPayload() e buildReservationsPayload() esistenti.
     */
    private function buildPeriodsPayload(string $revenueUnit, array &$warnings): array
    {
        $now = Carbon::now();

        $ranges = [
            'today'         => [$now->copy()->startOfDay(),   $now->copy()->endOfDay()],
            'last_7_days'   => [$now->copy()->subDays(6)->startOfDay(),  $now->copy()->endOfDay()],
            'last_30_days'  => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'current_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfDay()],
            'current_year'  => [$now->copy()->startOfYear(),  $now->copy()->endOfDay()],
            'all_time'      => [Carbon::create(2000, 1, 1)->startOfDay(), $now->copy()->endOfDay()],
        ];

        $result = [];

        foreach ($ranges as $key => [$from, $to]) {
            // Usa array separato per warnings di periodo: evitiamo duplicati con i
            // warning globali (buildOrdersPayload/buildReservationsPayload già li aggiunge).
            $periodWarnings = [];
            $orders         = $this->buildOrdersPayload($from, $to, $revenueUnit, $periodWarnings);
            $reservations   = $this->buildReservationsPayload($from, $to, $periodWarnings);

            $block = [
                'from'                => $from->toDateString(),
                'to'                  => $to->toDateString(),
                'orders_total'        => $orders['total'],
                'orders_revenue'      => $orders['revenue_confirmed'],
                'orders_average'      => ($orders['total'] > 0 && $orders['revenue_confirmed'] !== null)
                    ? round($orders['revenue_confirmed'] / $orders['total'], 2)
                    : 0,
                'orders_by_type'      => ['takeaway' => 0, 'delivery' => 0, 'table' => 0],
                'reservations_total'  => $reservations['total'],
                'reservations_covers' => $reservations['total_covers'],
            ];

            // Per il periodo all_time aggiungiamo il conteggio dei mesi con attività
            // reale, in modo che il backoffice possa calcolare medie mensili sensate
            // (es. 120 ordini in 3 mesi attivi = 40/mese, non 120/12 mesi).
            if ($key === 'all_time') {
                $block['orders_active_months']       = $this->countActiveMonths('orders',       $from, $to);
                $block['reservations_active_months'] = $this->countActiveMonths('reservations', $from, $to);
            }

            $result[$key] = $block;
        }

        return $result;
    }

    /**
     * Conta quanti mesi distinti hanno almeno un record nella tabella indicata.
     * Usato per calcolare medie mensili basate sull'attività reale, non sul periodo totale.
     */
    private function countActiveMonths(string $table, Carbon $from, Carbon $to): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'created_at')) {
            return 0;
        }

        try {
            $driver = DB::getDriverName();

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $result = DB::table($table)
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw("COUNT(DISTINCT DATE_FORMAT(created_at, '%Y-%m')) as active_months")
                    ->first();

                return (int) ($result->active_months ?? 0);
            }

            // Fallback SQLite (test env): raggruppa per strftime
            $result = DB::table($table)
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw("COUNT(DISTINCT strftime('%Y-%m', created_at)) as active_months")
                ->first();

            return (int) ($result->active_months ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Serie giornaliera degli ultimi 30 giorni (ordini e prenotazioni per giorno).
     * Restituisce esattamente 30 record, giorni senza dati hanno valori 0.
     */
    private function buildDailyPayload(string $revenueUnit, array &$warnings): array
    {
        $now   = Carbon::now();
        $start = $now->copy()->subDays(29)->startOfDay();
        $end   = $now->copy()->endOfDay();

        // Inizializza serie con 30 giorni a zero
        $series = [];
        for ($i = 0; $i < 30; $i++) {
            $date          = $now->copy()->subDays(29 - $i)->toDateString();
            $series[$date] = ['date' => $date, 'orders' => 0, 'revenue' => 0.0, 'reservations' => 0, 'covers' => 0];
        }

        // Ordini per giorno
        try {
            if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'created_at')) {
                $hasRevenue = in_array($revenueUnit, ['euros', 'cents'], true)
                    && Schema::hasColumn('orders', 'status')
                    && Schema::hasColumn('orders', 'tot_price');

                $select = $hasRevenue
                    ? 'DATE(created_at) as day, COUNT(*) as cnt, SUM(CASE WHEN status = 1 THEN tot_price ELSE 0 END) as rev'
                    : 'DATE(created_at) as day, COUNT(*) as cnt';

                $rows = DB::table('orders')
                    ->whereBetween('created_at', [$start, $end])
                    ->selectRaw($select)
                    ->groupByRaw('DATE(created_at)')
                    ->get();

                foreach ($rows as $row) {
                    $d = $row->day ?? null;
                    if ($d && isset($series[$d])) {
                        $series[$d]['orders']  = (int) $row->cnt;
                        $series[$d]['revenue'] = $hasRevenue
                            ? ($revenueUnit === 'cents'
                                ? (int) round($row->rev * 100)
                                : round((float) $row->rev, 2))
                            : 0.0;
                    }
                }
            }
        } catch (\Throwable $e) {
            $warnings[] = 'daily_orders_error: dati ordini giornalieri non disponibili (' . $e->getMessage() . ').';
        }

        // Prenotazioni per giorno
        try {
            if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'created_at')) {
                $rows = DB::table('reservations')
                    ->whereBetween('created_at', [$start, $end])
                    ->selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
                    ->groupByRaw('DATE(created_at)')
                    ->get();

                foreach ($rows as $row) {
                    $d = $row->day ?? null;
                    if ($d && isset($series[$d])) {
                        $series[$d]['reservations'] = (int) $row->cnt;
                    }
                }
            }
        } catch (\Throwable $e) {
            $warnings[] = 'daily_reservations_error: dati prenotazioni giornalieri non disponibili (' . $e->getMessage() . ').';
        }

        return array_values($series);
    }

    /**
     * Metriche di utilizzo del gestionale (menu, contenuti, admin).
     */
    private function buildUsagePayload(array &$warnings): array
    {
        $now          = Carbon::now();
        $ago7         = $now->copy()->subDays(7)->startOfDay();
        $ago30        = $now->copy()->subDays(30)->startOfDay();

        return [
            'menu'    => $this->buildUsageMenu($ago7, $ago30, $warnings),
            'content' => $this->buildUsageContent($ago7, $ago30, $warnings),
            'admin'   => $this->buildUsageAdmin($warnings),
        ];
    }

    private function buildUsageMenu(Carbon $ago7, Carbon $ago30, array &$warnings): array
    {
        $data = [
            'products_count'                  => 0,
            'categories_count'                => 0,
            'ingredients_count'               => 0,
            'last_product_updated_at'         => null,
            'last_category_updated_at'        => null,
            'last_ingredient_updated_at'      => null,
            'products_updated_last_7_days'    => 0,
            'products_updated_last_30_days'   => 0,
            'categories_updated_last_7_days'  => 0,
            'categories_updated_last_30_days' => 0,
            'ingredients_updated_last_7_days'  => 0,
            'ingredients_updated_last_30_days' => 0,
        ];

        foreach (['products', 'categories', 'ingredients'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            try {
                $prefix                   = rtrim($table, 's'); // products→product, etc.
                $countKey                 = $table . '_count';
                $data[$countKey]          = (int) DB::table($table)->count();

                $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');

                if ($hasUpdatedAt) {
                    $lastRaw = DB::table($table)->max('updated_at');
                    $data['last_' . $prefix . '_updated_at'] = $lastRaw
                        ? Carbon::parse($lastRaw)->toIso8601String()
                        : null;

                    $data[$table . '_updated_last_7_days']  = (int) DB::table($table)
                        ->where('updated_at', '>=', $ago7)->count();
                    $data[$table . '_updated_last_30_days'] = (int) DB::table($table)
                        ->where('updated_at', '>=', $ago30)->count();
                }
            } catch (\Throwable $e) {
                $warnings[] = "usage_menu_{$table}_error: " . $e->getMessage();
            }
        }

        return $data;
    }

    private function buildUsageContent(Carbon $ago7, Carbon $ago30, array &$warnings): array
    {
        $data = [
            'posts_count'                => 0,
            'posts_active'               => 0,
            'posts_promo'                => 0,
            'last_post_updated_at'       => null,
            'posts_updated_last_7_days'  => 0,
            'posts_updated_last_30_days' => 0,
        ];

        if (! Schema::hasTable('posts')) {
            return $data;
        }

        try {
            $data['posts_count'] = (int) DB::table('posts')->count();

            $activeQ = DB::table('posts');
            if (Schema::hasColumn('posts', 'visible'))  { $activeQ->where('visible', true); }
            if (Schema::hasColumn('posts', 'archived')) { $activeQ->where('archived', false); }
            $data['posts_active'] = (int) $activeQ->count();

            if (Schema::hasColumn('posts', 'promo')) {
                $promoQ = DB::table('posts')->where('promo', true);
                if (Schema::hasColumn('posts', 'visible'))  { $promoQ->where('visible', true); }
                if (Schema::hasColumn('posts', 'archived')) { $promoQ->where('archived', false); }
                $data['posts_promo'] = (int) $promoQ->count();
            }

            if (Schema::hasColumn('posts', 'updated_at')) {
                $lastRaw = DB::table('posts')->max('updated_at');
                $data['last_post_updated_at']       = $lastRaw ? Carbon::parse($lastRaw)->toIso8601String() : null;
                $data['posts_updated_last_7_days']  = (int) DB::table('posts')->where('updated_at', '>=', $ago7)->count();
                $data['posts_updated_last_30_days'] = (int) DB::table('posts')->where('updated_at', '>=', $ago30)->count();
            }
        } catch (\Throwable $e) {
            $warnings[] = 'usage_content_error: ' . $e->getMessage();
        }

        return $data;
    }

    private function buildUsageAdmin(array &$warnings): array
    {
        $data = ['last_admin_login_at' => null];

        if (! Schema::hasTable('users')) {
            return $data;
        }

        try {
            // Cerca last_login_at, poi updated_at come proxy per attività admin
            if (Schema::hasColumn('users', 'last_login_at')) {
                $raw = DB::table('users')->where('role', 'admin')->max('last_login_at');
                $data['last_admin_login_at'] = $raw ? Carbon::parse($raw)->toIso8601String() : null;
            } elseif (Schema::hasColumn('users', 'updated_at') && Schema::hasColumn('users', 'role')) {
                $raw = DB::table('users')->where('role', 'admin')->max('updated_at');
                $data['last_admin_login_at'] = $raw ? Carbon::parse($raw)->toIso8601String() : null;
            }
        } catch (\Throwable $e) {
            // Errore silenzioso: last_admin_login_at resta null, non è critico
        }

        return $data;
    }
}
