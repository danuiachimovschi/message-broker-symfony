<?php

declare(strict_types=1);

namespace App\Infrastructure\Avro\Interfaces;

use FlixTech\SchemaRegistryApi\AsynchronousRegistry;

interface SchemaRegistryClientInterface extends SchemaSerializerInterface, SchemaRegistryInterface
{
    public function initPromisingRegistry(): AsynchronousRegistry;
}