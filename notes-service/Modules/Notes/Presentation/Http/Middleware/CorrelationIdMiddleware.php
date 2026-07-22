<?php

namespace Modules\Notes\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelationIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $correlationId = $request->header('X-Correlation-ID');

        if (! $correlationId) {
            $correlationId = (string) Str::uuid();
        }

        $request->headers->set('X-Correlation-ID', $correlationId);

        $response = $next($request);

        $response->headers->set('X-Correlation-ID', $correlationId);

        return $response;
    }
}
