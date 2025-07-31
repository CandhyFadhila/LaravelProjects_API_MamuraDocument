<?php

namespace App\Http\Middleware;

use App\Http\Resources\Templates\WithoutDataResource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class CustomThrottle
{
    public function handle(Request $request, Closure $next, $maxAttempts = 5, $decayMinutes = 1)
    {
        $decaySeconds = $decayMinutes * 60;
        $user = $request->user();
        $key = ($user ? $user->id : $request->ip()) . '|' . $request->path();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json(
                new WithoutDataResource(
                    Response::HTTP_TOO_MANY_REQUESTS,
                    'Terlalu Banyak Permintaan',
                    'Anda terlalu banyak melakukan permintaan, coba lagi setelah ' . RateLimiter::availableIn($key) . ' detik.'
                ),
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        // Tambahkan hit untuk request yang masuk
        RateLimiter::hit($key, $decaySeconds);

        $response = $next($request);

        // Tambahkan informasi rate limit di response header
        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $maxAttempts - RateLimiter::attempts($key)),
            'X-RateLimit-Reset' => RateLimiter::availableIn($key),
        ]);
    }
}
