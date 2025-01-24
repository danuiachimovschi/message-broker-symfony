<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

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
    const TOTAL_PARTITIONS = 5;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $producer = KafkaProducerBuilder::create()
            ->withAdditionalBroker('kafka:9092')
            ->build();

        $csv = Reader::createFromPath(__DIR__ . '/../../../fixtures/users.csv', 'r');
        $csv->setHeaderOffset(0);

        $records = $csv->getRecords();

        foreach ($records as $record) {
            try {
                $message = KafkaProducerMessage::create('users', rand(0, self::TOTAL_PARTITIONS - 1))
                    ->withBody(json_encode($record, 1));

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
