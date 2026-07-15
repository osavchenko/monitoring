<?php
require __DIR__ . '/vendor/autoload.php';

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;
use OpenTelemetry\Contrib\Logs\Monolog\Handler as OTelMonologHandler;
use OpenTelemetry\API\Trace\Span;

$tracerProvider = Globals::tracerProvider();
$tracer = $tracerProvider->getTracer('my-php-app');

// Initialize Monolog
$logger = new Logger('my-php-app');

// Stream handler for stdout with JSON formatting
$streamHandler = new StreamHandler('php://stdout', Logger::INFO);
$streamHandler->setFormatter(new JsonFormatter());
$logger->pushHandler($streamHandler);

// OpenTelemetry handler for OTLP exporting
$otelHandler = new OTelMonologHandler(Globals::loggerProvider(), Logger::INFO);
$logger->pushHandler($otelHandler);

// Processor to automatically inject trace context into log records
$logger->pushProcessor(function (\Monolog\LogRecord $record) {
    $span = Span::getCurrent();
    $context = $span->getContext();
    $extra = $record->extra;
    $extra['trace_id'] = $context->getTraceId();
    $extra['span_id'] = $context->getSpanId();
    return $record->with(extra: $extra);
});

// Start a trace
$span = $tracer->spanBuilder(($_SERVER['REQUEST_METHOD'] ?? 'GET') . ' ' . ($_SERVER['REQUEST_URI'] ?? '/'))
    ->setSpanKind(SpanKind::KIND_SERVER)
    ->startSpan();

$scope = $span->activate();

try {
    // Add some custom attributes to the trace
    $span->setAttribute('app.environment', 'development');
    $span->setAttribute('app.user.id', random_int(1000, 9999));
    $span->setAttribute('http.client_ip', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

    // Simulate some work
    usleep(50000); // 50ms
    
    // Add an event to the span
    $span->addEvent('Work completed');
    
    // Log instead of echo. TraceId is automatically appended by the processor!
    $logger->info("App is running!"); 
} catch (\Throwable $t) {
    $span->recordException($t);
    http_response_code(500);
    
    // Log the actual exception details securely
    $logger->error("An error occurred", ['exception' => $t]);
} finally {
    $span->end();
    $scope->detach();
}
