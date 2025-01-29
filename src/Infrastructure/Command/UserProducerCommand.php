<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use AvroSchema;
use Exception;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use Jobcloud\Kafka\Message\Decoder\AvroDecoder;
use Jobcloud\Kafka\Message\Encoder\AvroEncoder;
use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistry;
use Jobcloud\Kafka\Producer\KafkaProducerBuilder;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'user/producer',
    description: 'User producer command',
)]
class UserProducerCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $schemaRegistryClient = new CachedRegistry(
            new PromisingRegistry(
                new Client(['base_uri' => 'schema-registry:8081'])
            ),
            new AvroObjectCacheAdapter()
        );

        $registry = new AvroSchemaRegistry($schemaRegistryClient);

        $recordSerializer = new RecordSerializer(
            $schemaRegistryClient,
            [
                RecordSerializer::OPTION_REGISTER_MISSING_SCHEMAS => false,
                RecordSerializer::OPTION_REGISTER_MISSING_SUBJECTS => true,
            ]
        );

        // Define Avro Schema
        $schema = <<<'JSON'
        {
            "type": "record",
            "name": "User",
            "fields": [
                {"name": "name", "type": "string"},
                {"name": "surname", "type": "string"},
                {"name": "email", "type": "string"}
            ]
        }
        JSON;

        $avroSchema = AvroSchema::parse($schema);

        $encoder = new AvroEncoder($registry, $recordSerializer);

        $producer = KafkaProducerBuilder::create()
            ->withAdditionalConfig(
                [
                    'compression.codec' => 'lz4',
                    'auto.commit.interval.ms' => 500
                ]
            )
            ->withEncoder($encoder)
            ->withAdditionalBroker('kafka:9092')
            ->build();

        $csv = Reader::createFromPath(__DIR__ . '/../../../fixtures/users.csv', 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        foreach ($records as $record) {
            try {
                $avroData = $recordSerializer->encodeRecord('User', $avroSchema, [
                    'name' => $record['Name'],
                    'surname' => $record['Surname'],
                    'email' => $record['Email']
                ]);

                $message = KafkaProducerMessage::create('users', RD_KAFKA_PARTITION_UA)
                    ->withBody($avroData);

                $producer->produce($message);
                $producer->flush(20000);

                $io->success('Message produced: '. $record['Name']);
            } catch (Exception $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
