<?php

declare(strict_types=1);

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MetricsController
{
    #[Route('/metrics', name: 'metrics')]
    public function number(int $max): Response
    {
        // Example metrics data
        $metrics = [
            'app_uptime_seconds' => 12345,
            'app_requests_total' => 6789,
            'app_active_users' => 42,
        ];

        // Convert metrics to Prometheus text format
        $prometheusMetrics = "";
        foreach ($metrics as $name => $value) {
            $prometheusMetrics .= "$name $value\n";
        }

        // Return response with Prometheus content type
        return new Response($prometheusMetrics, Response::HTTP_OK, [
            'Content-Type' => 'text/plain',
        ]);
    }
}