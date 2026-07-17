<?php

namespace App\Http\Middleware;

use App\Services\PrometheusService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PrometheusMiddleware
{
    public function __construct(
        private readonly PrometheusService $prometheus
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
if ($request->is('internal/metrics')) {
    return $next($request);
}
        $start = microtime(true);

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->record($request, 500, microtime(true) - $start);
            throw $exception;
        }

        $this->record(
            $request,
            $response->getStatusCode(),
            microtime(true) - $start
        );

        return $response;
    }

    private function record(Request $request, int $status, float $duration): void
    {
        $route = $request->route()?->getName()
            ?? $request->route()?->uri()
            ?? 'unmatched';

        $method = $request->getMethod();
        $statusGroup = sprintf('%dxx', intdiv($status, 100));

        $counter = $this->prometheus
            ->registry()
            ->getOrRegisterCounter(
                'laravel',
                'http_requests_total',
                'Nombre total de requêtes HTTP Laravel',
                ['method', 'route', 'status']
            );

        $counter->inc([
            $method,
            $route,
            $statusGroup,
        ]);

        $histogram = $this->prometheus
            ->registry()
            ->getOrRegisterHistogram(
                'laravel',
                'http_request_duration_seconds',
                'Durée des requêtes HTTP Laravel',
                ['method', 'route'],
                [0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5]
            );

        $histogram->observe($duration, [
            $method,
            $route,
        ]);
    }
}
