<?php

namespace Modules\Notes\Infrastructure\Tracing;

use Closure;
use Throwable;

class TraceManager
{
    public function __construct(
        private OpenTelemetryTracer $otel,
    ) {}

    /**
     * Execute code inside a span.
     */
    public function span(
        string $name,
        Closure $callback,
        array $attributes = []
    ) {
        $tracer = $this->otel->tracer();

        $span = $tracer
            ->spanBuilder($name)
            ->startSpan();

        $scope = $span->activate();

        foreach ($attributes as $key => $value) {
            $span->setAttribute($key, $value);
        }

        try {

            return $callback();

        } catch (Throwable $e) {

            $span->recordException($e);
            $span->setAttribute('error', true);

            throw $e;
        } finally {

            $scope->detach();
            $span->end();
        }
    }
}
