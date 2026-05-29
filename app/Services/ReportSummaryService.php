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
        $warnings = [];
        $revenueUnit = config('configurazione.private_report_revenue_unit', 'euros');

        $ordersPayload      = $this->buildOrdersPayload($from, $to, $revenueUnit, $warnings);
        $reservationsPayload = $this->buildReservationsPayload($from, $to, $warnings);

        return [
            'api_version'   => '1',
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
            'data_warnings' => $warnings,
            'generated_at'  => now()->toIso8601String(),
        ];
    }

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
            if (!Schema::hasTable('orders')) {
                $warnings[] = 'orders_table_missing: tabella orders non trovata in questo database.';
                return ['total' => 0, 'revenue_confirmed' => null, 'available' => false];
            }

            $query = DB::table('orders')
                ->whereBetween('created_at', [
                    $from->copy()->startOfDay(),
                    $to->copy()->endOfDay(),
                ]);

            $total = (clone $query)->count();

            // Il revenue è calcolato solo per unità monetarie note
            $revenueConfirmed = null;
            if (in_array($revenueUnit, ['euros', 'cents'], true)) {
                if (!Schema::hasColumn('orders', 'tot_price') || !Schema::hasColumn('orders', 'status')) {
                    $warnings[] = 'orders_revenue_columns_missing: colonne tot_price o status non trovate, revenue non calcolabile.';
                } else {
                    // status = 1 corrisponde a "confermato" nel progetto
                    $rawRevenue = (clone $query)
                        ->where('status', 1)
                        ->sum('tot_price');

                    // tot_price è già in formato decimale (es. 12.50 = €12,50)
                    // Se l'unità richiesta è cents, moltiplichiamo
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
     *
     * Usa created_at per il filtro per data. Non si appoggia alla tabella
     * dates (che ha colonne year/month/day come interi separati) per evitare
     * JOIN costosi e per coerenza con il filtro degli ordini.
     *
     * n_person è una stringa JSON con chiavi "adult" e "child" — la somma
     * dei coperti viene calcolata con JSON_EXTRACT su MySQL o con un ciclo
     * PHP su SQLite (ambiente di test).
     */
    private function buildReservationsPayload(
        Carbon $from,
        Carbon $to,
        array &$warnings
    ): array {
        try {
            if (!Schema::hasTable('reservations')) {
                $warnings[] = 'reservations_table_missing: tabella reservations non trovata in questo database.';
                return ['total' => 0, 'total_covers' => 0, 'available' => false];
            }

            $query = DB::table('reservations')
                ->whereBetween('created_at', [
                    $from->copy()->startOfDay(),
                    $to->copy()->endOfDay(),
                ]);

            $total = (clone $query)->count();

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
     *
     * Su MySQL usa JSON_EXTRACT per efficienza.
     * Su altri driver carica i valori in PHP e somma manualmente (dataset
     * delle prenotazioni tipicamente contenuto, non è un problema).
     */
    private function sumCovers(\Illuminate\Database\Query\Builder $baseQuery, array &$warnings): int
    {
        if (!Schema::hasColumn('reservations', 'n_person')) {
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

            // Fallback PHP per SQLite o altri driver
            $rows = (clone $baseQuery)->select('n_person')->get();
            $sum = 0;
            foreach ($rows as $row) {
                $parsed = json_decode($row->n_person ?? '{}', true);
                if (is_array($parsed)) {
                    $sum += (int) ($parsed['adult'] ?? 0);
                    $sum += (int) ($parsed['child'] ?? 0);
                }
            }
            return $sum;
        } catch (\Throwable $e) {
            Log::error('ReportSummaryService: errore nel calcolo coperti', [
                'message' => $e->getMessage(),
            ]);
            $warnings[] = 'reservations_covers_error: impossibile sommare i coperti (' . $e->getMessage() . ').';
            return 0;
        }
    }
}
