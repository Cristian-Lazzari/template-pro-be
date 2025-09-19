<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-API-KEY');

        if (!$key || $key !== env('API_KEY_SECRET')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
