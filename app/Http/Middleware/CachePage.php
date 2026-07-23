<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class CachePage
{
    public function handle($request, Closure $next)
    {
        if (!$request->isMethod('get') || $request->ajax()) {
            return $next($request);
        }

        $key = 'page_' . md5($request->fullUrl());

        if (Cache::has($key)) {
            return response(Cache::get($key));
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::put($key, $response->getContent(), 300); // 5 minutos
        }

        return $response;
    }
}
