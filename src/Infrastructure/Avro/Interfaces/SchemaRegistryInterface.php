<?php

declare(strict_types=1);

namespace App\Infrastructure\Avro\Interfaces;

use FlixTech\SchemaRegistryApi\Registry;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistryInterface;

interface SchemaRegistryInterface
{
    public function getRegistry(): Registry;

    public function getSchemaRegistryClient(): AvroSchemaRegistryInterface;
}