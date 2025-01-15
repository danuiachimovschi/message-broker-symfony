<?php

namespace App\Command;

use Jobcloud\Kafka\Consumer\KafkaConsumerBuilder;
use Jobcloud\Kafka\Exception\KafkaConsumerConsumeException;
use Jobcloud\Kafka\Exception\KafkaConsumerEndOfPartitionException;
use Jobcloud\Kafka\Exception\KafkaConsumerTimeoutException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'kafka-consumer',
    description: 'Kafka consumer command',
)]
class KakfaConsumerCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $consumer = KafkaConsumerBuilder::create()
            ->withAdditionalConfig(
                [
                    'enable.auto.commit' => false
                ]
            )
            ->withAdditionalBroker('kafka:9092')
            ->withConsumerGroup('testGroup')
            ->withAdditionalSubscription('authors')
            ->build();

        $consumer->subscribe();

        while (true) {
            try {
                $message = $consumer->consume();
                $io->success($message->getBody());

                $consumer->commit($message);
            } catch (KafkaConsumerTimeoutException|KafkaConsumerEndOfPartitionException $e) {
            } catch (KafkaConsumerConsumeException $e) {
                return Command::FAILURE;
            }
        }
    }
}
