<?php

declare(strict_types=1);

namespace App\Infrastructure\Avro\Interfaces;

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use Jobcloud\Kafka\Message\Decoder\AvroDecoderInterface;
use Jobcloud\Kafka\Message\Encoder\AvroEncoderInterface;

interface SchemaSerializerInterface
{
    public function getRecordSerializer(): RecordSerializer;

    public function getEncoder(): AvroEncoderInterface;

    public function getDecoder(): AvroDecoderInterface;
}