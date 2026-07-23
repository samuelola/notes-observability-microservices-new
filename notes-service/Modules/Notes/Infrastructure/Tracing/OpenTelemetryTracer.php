<?php

namespace Modules\Notes\Infrastructure\Tracing;

use OpenTelemetry\API\Trace\NoopTracer;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SemConv\ResourceAttributes;

class OpenTelemetryTracer
{
    private ?TracerProvider $provider = null;

    public function __construct()
    {
        if (! env('OTEL_ENABLED', true)) {
            return;
        }
        $transport = (new OtlpHttpTransportFactory)->create(
            env('OTEL_EXPORTER_OTLP_ENDPOINT', 'http://jaeger:4318/v1/traces'),
            'application/x-protobuf'
        );

        $resource = ResourceInfo::create(
            Attributes::create([
                ResourceAttributes::SERVICE_NAME => 'notes-service',
            ])
        );

        $exporter = new SpanExporter($transport);

        $this->provider = new TracerProvider(
            new SimpleSpanProcessor($exporter),
            null,
            $resource
        );
    }

    public function tracer()
    {
        if ($this->provider === null) {
            return new NoopTracer;
        }

        return $this->provider->getTracer('notes-service');
    }

    public function shutdown(): void
    {
        if ($this->provider !== null) {
            $this->provider->shutdown();
        }
    }
}
