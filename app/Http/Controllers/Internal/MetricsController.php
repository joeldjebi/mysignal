<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Services\PrometheusService;
use Prometheus\RenderTextFormat;
use Symfony\Component\HttpFoundation\Response;

class MetricsController extends Controller
{
    public function __invoke(PrometheusService $prometheus): Response
    {
        $renderer = new RenderTextFormat();

        $metrics = $renderer->render(
            $prometheus->registry()->getMetricFamilySamples()
        );

        return response($metrics, 200, [
            'Content-Type' => RenderTextFormat::MIME_TYPE,
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
