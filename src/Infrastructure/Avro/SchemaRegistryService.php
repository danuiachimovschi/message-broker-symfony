<?php

declare(strict_types=1);

namespace App\Infrastructure\Avro;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class SchemaRegistryService
{
    private string $url;

    private bool $register_missing_schemas;

    private bool $register_missing_subjects;

    public function __construct(ParameterBagInterface $params)
    {
        $this->url = $params->get('schema_registry.url');
        $this->register_missing_schemas = (bool) $params->get('schema_registry.register_missing_schemas');
        $this->register_missing_subjects = (bool) $params->get('schema_registry.register_missing_subjects');
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getRegisterMissingSchemas(): bool
    {
        return $this->register_missing_schemas;
    }

    public function getRegisterMissingSubjects(): bool
    {
        return $this->register_missing_subjects;
    }
}