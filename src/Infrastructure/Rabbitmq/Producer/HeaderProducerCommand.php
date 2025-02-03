<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Producer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'rabbitmq/producer-header',
    description: 'Rabbitmq second consumer header command',
)]
class HeaderProducerCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('events_header', 'headers', false, true, false);

        foreach (range(1, 1000) as $i) {
            $msg = new AMQPMessage(json_encode(['Hello World! ' . $i]));
            $msg->set('application_headers', new AMQPTable([
                'format' => 'json',
                'type' => 'log',
            ]));
            $msg->set('content_type', 'application/json');
            $msg->set('priority', rand(0, 10));

            $channel->basic_publish($msg, 'events_header');
            $io->info(sprintf(' [x] Sent %s', $i));
        }

        $channel->close();
        $connection->close();

        $io->info('Rabbitmq producer direct command');

        return Command::SUCCESS;
    }
}