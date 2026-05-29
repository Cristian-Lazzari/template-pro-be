<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ValidatePrivateReportToken
{
    /**
     * Valida il token Bearer per le API private del report.
     *
     * Restituisce 503 se PRIVATE_REPORT_TOKEN non è configurato nell'istanza
     * (token vuoto = feature non attivata, non un errore del client).
     * Restituisce 401 se il token è presente ma non corrisponde.
     * Usa hash_equals per prevenire timing attacks.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $configuredToken = config('configurazione.private_report_token');

        // Token non configurato in questa istanza: feature non attiva
        if (empty($configuredToken)) {
            return response()->json([
                'error' => 'private_report_not_configured',
                'message' => 'This instance has not configured a private report token.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $bearerToken = $request->bearerToken();

        // Token assente o non corrispondente
        if (empty($bearerToken) || !hash_equals($configuredToken, $bearerToken)) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'Invalid or missing authorization token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
