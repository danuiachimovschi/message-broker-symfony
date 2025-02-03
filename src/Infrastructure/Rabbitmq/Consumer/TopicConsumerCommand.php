<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Consumer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'rabbitmq/consumer-topic',
    description: 'Rabbitmq second consumer topic command',
)]
class TopicConsumerCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): never
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('events_topic', 'topic', false, true, false);

        [$queue_name, ,] = $channel->queue_declare("events.client4", false, true, true, false);

        $channel->queue_bind($queue_name, 'events_topic', 'events.*');

        $callback = function ($msg) {
            if ($msg->body) {
                echo ' [x] Received ', $msg->body, "\n";
            }
        };

        $channel->basic_consume('events.client4', '', false, true, false, false, $callback);

        while (true) {
            $channel->wait();
        }
    }
}