<?php

declare(strict_types=1);

namespace App\Infrastructure\Neo4j\Core;

use Laudis\Neo4j\Contracts\ClientInterface;

interface NeoConnectionInterface
{
    public function getClient(): ClientInterface;
}