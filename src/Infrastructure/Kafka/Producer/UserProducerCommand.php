<?php

declare(strict_types=1);

namespace App\Infrastructure\Kafka\Producer;

use App\Infrastructure\Avro\Interfaces\SchemaRegistryClientInterface;
use Exception;
use Jobcloud\Kafka\Message\KafkaProducerMessage;
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
    private const SCHEMA_NAME = 'User';

    private const TOPIC_NAME = 'users';

    public function __construct(
        protected readonly SchemaRegistryClientInterface $schemaRegistryClient,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $avroSchema = $this->schemaRegistryClient->getRegistry()->latestVersion('User')->wait();

        $producer = KafkaProducerBuilder::create()
            ->withAdditionalConfig(
                [
                    'compression.codec' => 'lz4',
                    'auto.commit.interval.ms' => 500
                ]
            )
            ->withEncoder($this->schemaRegistryClient->getEncoder())
            ->withAdditionalBroker('kafka:9092')
            ->build();

        $csv = Reader::createFromPath(__DIR__ . '/../../../../fixtures/users.csv', 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        foreach ($records as $record) {
            try {
                $avroData = $this->schemaRegistryClient->getRecordSerializer()->encodeRecord(self::SCHEMA_NAME, $avroSchema, [
                    'name' => $record['Name'],
                    'surname' => $record['Surname'],
                    'email' => $record['Email']
                ]);

                $message = KafkaProducerMessage::create(self::TOPIC_NAME, RD_KAFKA_PARTITION_UA)
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
