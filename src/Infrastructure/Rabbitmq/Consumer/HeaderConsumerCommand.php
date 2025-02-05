<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Consumer;

use App\Infrastructure\Rabbitmq\RabbitmqConnectionInterface;
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
    private const EXCHANGE_NAME = 'e.header';

    private const QUEUE_NAME = 'q.events-header';

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

        $channel->exchange_declare(
            exchange: self::EXCHANGE_NAME,
            type: 'headers',
            durable: true,
            auto_delete: false,
            arguments: new AMQPTable(['x-max-priority' => 10])
        );

        [$queue_name, ,] = $channel->queue_declare(self::QUEUE_NAME, false, true, true, false);

        $headers = new AMQPTable([
            'x-match' => 'all',
            'format' => 'json',
            'type' => 'log'
        ]);

        $channel->queue_bind($queue_name, self::EXCHANGE_NAME, '', $headers);

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