<?php

declare(strict_types=1);

namespace App\Infrastructure\Elastic\Core;

use Elastic\Elasticsearch\ClientInterface;

interface ElasticSearchConnectionInterface
{
    public function getClient(): ClientInterface;
}