<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportSummaryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PrivateReportController extends Controller
{
    public function __construct(private readonly ReportSummaryService $reportService)
    {
    }

    /**
     * GET /api/private/report-summary
     *
     * Restituisce le metriche aggregate dell'istanza per il backoffice centrale.
     * Parametri opzionali: from (Y-m-d), to (Y-m-d).
     * Default: mese corrente (dal primo all'ultimo giorno).
     */
    public function summary(Request $request): JsonResponse
    {
        [$from, $to, $validationError] = $this->resolvePeriod($request);

        if ($validationError !== null) {
            return response()->json([
                'error'   => 'invalid_period',
                'message' => $validationError,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $this->reportService->generate($from, $to);

        return response()->json($payload);
    }

    /**
     * Risolve e valida i parametri from/to.
     *
     * @return array{Carbon, Carbon, string|null}
     */
    private function resolvePeriod(Request $request): array
    {
        $rawFrom = $request->query('from');
        $rawTo   = $request->query('to');

        // Nessun parametro: periodo di default = mese corrente
        if ($rawFrom === null && $rawTo === null) {
            $from = Carbon::now()->startOfMonth()->startOfDay();
            $to   = Carbon::now()->endOfMonth()->endOfDay();
            return [$from, $to, null];
        }

        // Se uno solo è fornito, errore esplicito
        if ($rawFrom === null || $rawTo === null) {
            return [Carbon::now(), Carbon::now(), 'I parametri from e to devono essere forniti entrambi oppure nessuno dei due.'];
        }

        try {
            $from = Carbon::createFromFormat('Y-m-d', $rawFrom)->startOfDay();
        } catch (\Exception) {
            return [Carbon::now(), Carbon::now(), "Il parametro from non è una data valida (formato atteso: Y-m-d)."];
        }

        try {
            $to = Carbon::createFromFormat('Y-m-d', $rawTo)->endOfDay();
        } catch (\Exception) {
            return [Carbon::now(), Carbon::now(), "Il parametro to non è una data valida (formato atteso: Y-m-d)."];
        }

        if ($from->greaterThan($to)) {
            return [Carbon::now(), Carbon::now(), 'Il parametro from deve essere precedente o uguale a to.'];
        }

        return [$from, $to, null];
    }
}
