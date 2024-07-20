<?php

namespace Paharok\Laravelfiles\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoCache
{

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        return $response->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
