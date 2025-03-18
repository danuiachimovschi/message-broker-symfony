<?php

declare(strict_types=1);

namespace App\Infrastructure\Neo4j\Core;

use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;

final readonly class NeoConnection implements NeoConnectionInterface
{
    private ClientInterface $client;
    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->withDriver('bolt', 'bolt://neo4j:strongpassword123@neo4j:7687')
            ->withDefaultDriver('bolt')
            ->build();
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}