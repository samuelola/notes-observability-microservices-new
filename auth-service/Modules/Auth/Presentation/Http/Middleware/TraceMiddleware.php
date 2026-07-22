<?php

namespace Modules\Auth\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\Infrastructure\Tracing\OpenTelemetryTracer;
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

        $traceId = $span
            ->getContext()
            ->getTraceId();

        $span->setAttribute('http.method', $request->method());

        $span->setAttribute('http.route', $request->path());

        $span->setAttribute('http.url', $request->fullUrl());

        $span->setAttribute('client.ip', $request->ip());

        $span->setAttribute('trace_id', $traceId);

        $request->headers->set('trace_id', $traceId);

        try {

            $response = $next($request);

            $span->setAttribute(
                'http.status_code',
                $response->getStatusCode()
            );

            $response->headers->set('trace_id', $traceId);

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
