<?php

namespace Modules\Notes\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Notes\Infrastructure\Tracing\OpenTelemetryTracer;
use Throwable;

class TraceMiddleware
{
    public function __construct(
        private OpenTelemetryTracer $otel,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $tracer = $this->otel->tracer();

        $span = $tracer
            ->spanBuilder($request->method().' '.$request->path())
            ->startSpan();

        $scope = $span->activate();
        $span->setAttribute('http.method', $request->method());

        $span->setAttribute('http.route', $request->path());

        $span->setAttribute('http.url', $request->fullUrl());

        $span->setAttribute('client.ip', $request->ip());

        $traceId = $span
            ->getContext()
            ->getTraceId();

        $request->attributes->set('trace_id', $traceId);

        try {

            $response = $next($request);

            $span->setAttribute(
                'http.status_code',
                $response->getStatusCode()
            );

            return $response;

        } catch (Throwable $e) {

            $span->recordException($e);
            $span->setAttribute('error', true);

            throw $e;
        } finally {

            $scope->detach();

            $span->end();

            $this->otel->shutdown();
        }

        return $next($request);
    }
}
