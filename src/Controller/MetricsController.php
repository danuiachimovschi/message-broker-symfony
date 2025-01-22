<?php

declare(strict_types=1);

namespace App\Controller;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MetricsController
{
    #[Route('/metrics', name: 'metrics')]
    public function metrics(): Response
    {
        $registry = new CollectorRegistry(new Redis());

        // Render all metrics in Prometheus format
        $renderer = new RenderTextFormat();
        $metrics = $renderer->render($registry->getMetricFamilySamples());

        // Return metrics as a plain text response
        return new Response($metrics, 200, ['Content-Type' => 'text/plain']);
    }
}