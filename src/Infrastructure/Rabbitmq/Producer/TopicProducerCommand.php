<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Producer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'rabbitmq/producer-topic',
    description: 'Rabbitmq producer topic command',
)]
class TopicProducerCommand  extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->exchange_declare('events_topic', 'topic', false, true, false);

        foreach (range(1, 1000) as $i) {
            $msg = new AMQPMessage(json_encode(['Hello World! ' . $i]));
            $channel->basic_publish($msg, 'events_topic', "events.$i");
            $io->info(sprintf(' [x] Sent %s', $i));
        }

        $channel->close();
        $connection->close();

        $io->info('Rabbitmq producer direct command');

        return Command::SUCCESS;
    }
}