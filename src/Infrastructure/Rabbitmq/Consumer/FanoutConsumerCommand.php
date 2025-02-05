<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Consumer;

use App\Infrastructure\Rabbitmq\RabbitmqConnection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'rabbitmq/consumer-fanout',
    description: 'Rabbitmq consumer fanout command',
)]
class FanoutConsumerCommand extends Command
{
    private const EXCHANGE_NAME = 'e.fanout';

    private const QUEUE_NAME = 'q.events-fanout.1';

    public function __construct(
        protected readonly RabbitmqConnection $rabbitmqConnection,
        ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): never
    {
        $connection = $this->rabbitmqConnection->getConnection();
        $channel = $connection->channel();

        $channel->exchange_declare(self::EXCHANGE_NAME, 'fanout', false, true, false);

        [$queue_name, ,] = $channel->queue_declare(self::QUEUE_NAME, false, true, true, false);

        $channel->queue_bind($queue_name, self::EXCHANGE_NAME);

        $callback = static function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
        };

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while (true) {
            $channel->wait();
        }
    }
}
