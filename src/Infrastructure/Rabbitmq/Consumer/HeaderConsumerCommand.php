<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Consumer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'rabbitmq/consumer-header',
    description: 'Rabbitmq consumer header command',
)]
class HeaderConsumerCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): never
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('events_header', 'headers', false, true, false);

        [$queue_name, ,] = $channel->queue_declare("events.client5", false, true, true, false);

        $headers = new AMQPTable([
            'x-match' => 'all',
            'format' => 'json',
            'type' => 'log'
        ]);

        $channel->queue_bind($queue_name, 'events_header', '', $headers);

        $callback = function ($msg) {
            if ($msg->body) {
                echo ' [x] Received ', $msg->body, "\n";
            }
        };

        $channel->basic_consume('events.client5', '', false, true, false, false, $callback);

        while (true) {
            $channel->wait();
        }
    }
}