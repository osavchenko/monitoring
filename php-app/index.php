<?php
require __DIR__ . '/vendor/autoload.php';

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;

$tracerProvider = Globals::tracerProvider();
$tracer = $tracerProvider->getTracer('my-php-app');

// Start a trace
$span = $tracer->spanBuilder($_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'])
    ->setSpanKind(SpanKind::KIND_SERVER)
    ->startSpan();

$scope = $span->activate();

try {
    // Simulate some work
    usleep(50000); // 50ms
    
    // Add an event to the span
    $span->addEvent('Work completed');
    
    $traceId = $span->getContext()->getTraceId();
    echo "App is running! Trace generated with ID: " . $traceId . "\n";
} catch (\Throwable $t) {
    $span->recordException($t);
    http_response_code(500);
    echo "An error occurred.\n";
} finally {
    $span->end();
    $scope->detach();
}
