<?php

declare(strict_types=1);

namespace App\Infrastructure\Avro;

use App\Infrastructure\Avro\Interfaces\SchemaRegistryClientInterface;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\AsynchronousRegistry;
use FlixTech\SchemaRegistryApi\Registry;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use Jobcloud\Kafka\Message\Decoder\AvroDecoder;
use Jobcloud\Kafka\Message\Decoder\AvroDecoderInterface;
use Jobcloud\Kafka\Message\Encoder\AvroEncoder;
use Jobcloud\Kafka\Message\Encoder\AvroEncoderInterface;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistry;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistryInterface;

class ConfluenceSchemaRegistryClient implements SchemaRegistryClientInterface
{
    private readonly Registry $cachedRegistry;

    private readonly AvroSchemaRegistryInterface $schemaRegistryClient;

    private readonly RecordSerializer $recordSerializer;

    private readonly array $schemaClientConfig;

    public function __construct(SchemaRegistryService $schemaRegistryService)
    {
        $this->schemaClientConfig = ['base_uri' => $schemaRegistryService->getUrl()];
        $this->cachedRegistry = new CachedRegistry(
            new PromisingRegistry(
                new Client($this->schemaClientConfig)
            ),
            new AvroObjectCacheAdapter()
        );
        $this->schemaRegistryClient = new AvroSchemaRegistry($this->cachedRegistry);

        $this->recordSerializer = new RecordSerializer(
            $this->cachedRegistry,
            [
                RecordSerializer::OPTION_REGISTER_MISSING_SCHEMAS => $schemaRegistryService->getRegisterMissingSchemas(),
                RecordSerializer::OPTION_REGISTER_MISSING_SUBJECTS => $schemaRegistryService->getRegisterMissingSubjects(),
            ]
        );
    }

    public function getRegistry(): Registry
    {
        return $this->cachedRegistry;
    }

    public function getSchemaRegistryClient(): AvroSchemaRegistryInterface
    {
        return $this->schemaRegistryClient;
    }

    public function getRecordSerializer(): RecordSerializer
    {
        return $this->recordSerializer;
    }

    public function getEncoder(): AvroEncoderInterface
    {
        return new AvroEncoder($this->schemaRegistryClient, $this->recordSerializer);
    }

    public function getDecoder(): AvroDecoderInterface
    {
        return new AvroDecoder($this->schemaRegistryClient, $this->recordSerializer);
    }

    public function initPromisingRegistry(): AsynchronousRegistry
    {
        return new PromisingRegistry(
            new Client($this->schemaClientConfig)
        );
    }
}