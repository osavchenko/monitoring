<?php

namespace App;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use Monolog\Logger;
use OpenTelemetry\Contrib\Logs\Monolog\Handler as OTelMonologHandler;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use Psr\Log\LoggerInterface;

class OTelHandlerFactory
{
    public static function create(LoggerProviderInterface $loggerProvider): OTelMonologHandler
    {
        return new OTelMonologHandler($loggerProvider, Logger::INFO);
    }
}

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'S3CRET',
            'http_method_override' => false,
            'router' => [
                'utf8' => true,
            ]
        ]);

        $container->extension('monolog', [
            'handlers' => [
                'main' => [
                    'type' => 'stream',
                    'path' => 'php://stdout',
                    'level' => 'info',
                    'formatter' => 'monolog.formatter.json',
                ],
                'otel' => [
                    'type' => 'service',
                    'id' => 'otel_monolog_handler',
                ]
            ],
        ]);

        $services = $container->services();
        
        $services->set(LoggerProviderInterface::class)
            ->factory([Globals::class, 'loggerProvider']);

        $services->set('otel_monolog_handler', OTelMonologHandler::class)
            ->factory([OTelHandlerFactory::class, 'create'])
            ->args([
                new \Symfony\Component\DependencyInjection\Reference(LoggerProviderInterface::class)
            ]);

        $services->set('otel_processor', \Closure::class)
            ->factory([\Closure::class, 'fromCallable'])
            ->args([[self::class, 'logProcessor']])
            ->tag('monolog.processor');

        $services->set(TracerProviderInterface::class)
            ->factory([Globals::class, 'tracerProvider']);

        $services->set(AppController::class)
            ->autowire()
            ->autoconfigure()
            ->public();
    }

    public static function logProcessor(\Monolog\LogRecord $record)
    {
        $span = Span::getCurrent();
        $context = $span->getContext();
        $extra = $record->extra;
        $extra['trace_id'] = $context->getTraceId();
        $extra['span_id'] = $context->getSpanId();
        return $record->with(extra: $extra);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->add('index', '/')->controller([AppController::class, 'index']);
    }

    public function getCacheDir(): string
    {
        return __DIR__ . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return __DIR__ . '/var/log/' . $this->environment;
    }
}

class AppController
{
    private LoggerInterface $logger;
    private TracerProviderInterface $tracerProvider;

    public function __construct(LoggerInterface $logger, TracerProviderInterface $tracerProvider)
    {
        $this->logger = $logger;
        $this->tracerProvider = $tracerProvider;
    }

    public function index(Request $request): Response
    {
        $tracer = $this->tracerProvider->getTracer('my-php-app');

        $span = $tracer->spanBuilder($request->getMethod() . ' ' . $request->getPathInfo())
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->startSpan();

        $scope = $span->activate();

        try {
            $span->setAttribute('app.environment', 'development');
            $span->setAttribute('app.user.id', random_int(1000, 9999));
            $span->setAttribute('http.client_ip', $request->getClientIp() ?? '127.0.0.1');

            usleep(50000); // 50ms
            
            $span->addEvent('Work completed', [
                'work.completed' => true,
            ]);
            
            $this->logger->info("App is running!"); 
            
            return new Response("App is running!", 200);
        } catch (\Throwable $t) {
            $span->recordException($t);
            $this->logger->error("An error occurred", ['exception' => $t]);
            return new Response("An error occurred", 500);
        } finally {
            $span->end();
            $scope->detach();
        }
    }
}

$kernel = new Kernel('dev', false);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
