<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Consumer;

use App\Infrastructure\Rabbitmq\RabbitmqConnectionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'rabbitmq/consumer-direct',
    description: 'Rabbitmq consumer direct command',
)]
class DirectConsumerCommand extends Command
{
    private const QUEUE_NAME = 'q.events-direct';

    private const EXCHANGE_NAME = 'e.direct';

    public function __construct(
        protected readonly RabbitmqConnectionInterface $rabbitmqConnection,
        ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): never
    {
        $connection = $this->rabbitmqConnection->getConnection();
        $channel = $connection->channel();

        $channel->exchange_declare(self::EXCHANGE_NAME, 'direct', false, true, false);

        [$queue_name, ,] = $channel->queue_declare(self::QUEUE_NAME, false, true, true, false);

        $channel->queue_bind($queue_name, self::EXCHANGE_NAME);

        $callback = static function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
        };

        $channel->basic_consume(self::QUEUE_NAME, '', false, true, false, false, $callback);

        /** @phpstan-ignore while.alwaysTrue */
        while (true) {
            $channel->wait();
        }
    }
}
