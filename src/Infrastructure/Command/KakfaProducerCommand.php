<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use Exception;
use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Jobcloud\Kafka\Producer\KafkaProducerBuilder;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'kafka-producer',
    description: 'Kafka producer command',
)]
class KakfaProducerCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $registry = new CollectorRegistry(new Redis());

        $requestCounter = $registry->getOrRegisterCounter(
            'kafka',
            'producer_messages_total',
            'Total number of messages produced'
        );

        $producer = KafkaProducerBuilder::create()
            ->withAdditionalBroker('kafka:9092')
            ->build();

        try {
            $message = KafkaProducerMessage::create('authors', 0)
                ->withKey('asdf-asdf-asfd-asdf')
                ->withBody('some test message payload')
                ->withHeaders([ 'key' => 'value' ]);

            $producer->produce($message);
        } catch (Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $producer->flush(20000);
        $requestCounter->incBy(1);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
