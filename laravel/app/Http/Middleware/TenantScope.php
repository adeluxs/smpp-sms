<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TenantScope
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->user()) {
            app()->instance('tenant', $request->user()->tenant);
            $request->user()->setRelation('tenant', $request->user()->tenant);
        }
        
        return $next($request);
    }
}