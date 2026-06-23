<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SmppRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->has('system_id')) {
            return response()->json(['error' => 'Missing system_id'], 400);
        }

        $key = 'smpp:bind:' . $request->input('system_id');
        $maxAttempts = 10;
        $decayMinutes = 1;

        if (Cache::has($key)) {
            $attempts = Cache::get($key, 0);
            if ($attempts >= $maxAttempts) {
                return response()->json(['error' => 'Rate limit exceeded'], 429);
            }
        }

        Cache::add($key, 1, $decayMinutes * 60);

        return $next($request);
    }
}