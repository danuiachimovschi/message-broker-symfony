<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Consumer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'rabbitmq/consumer-direct',
    description: 'Rabbitmq consumer direct command',
)]
class DirectConsumerCommand extends Command
{
    public function __construct(
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): never
    {
        $io = new SymfonyStyle($input, $output);

        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $callback = function ($msg) {
            if ($msg->body) {
                echo ' [x] Received ', $msg->body, "\n";
            }
        };

        $channel->basic_consume('daniel', '', false, true, false, false, $callback);

        while (true) {
            $channel->wait();
        }
    }
}
