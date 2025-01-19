<?php

namespace App\Command;

use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Jobcloud\Kafka\Producer\KafkaProducerBuilder;
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

        $producer = KafkaProducerBuilder::create()
            ->withAdditionalBroker('kafka:9092')
            ->build();

        $message = KafkaProducerMessage::create('authors', 0)
            ->withKey('asdf-asdf-asfd-asdf')
            ->withBody('some test message payload')
            ->withHeaders([ 'key' => 'value' ]);

        $producer->produce($message);

        $producer->flush(20000);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
