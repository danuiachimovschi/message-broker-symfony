<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Producer;

use App\Infrastructure\Rabbitmq\RabbitmqConnection;
use App\Infrastructure\Rabbitmq\RabbitmqConnectionInterface;
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
    private const EXCHANGE_NAME = 'e.header';

    public function __construct(
        protected readonly RabbitmqConnectionInterface $rabbitmqConnection,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connection = $this->rabbitmqConnection->getConnection();
        $channel = $connection->channel();

        $channel->exchange_declare(
            exchange: self::EXCHANGE_NAME,
            type: 'headers',
            durable: true,
            auto_delete: false,
            arguments: new AMQPTable(['x-max-priority' => 10])
        );

        foreach (range(1, 1000) as $i) {
            $msg = new AMQPMessage(json_encode(['Hello World! ' . $i]));

            $msg->set('application_headers', new AMQPTable([
                'format' => 'json',
                'type' => 'log',
            ]));
            $msg->set('content_type', 'application/json');
            $msg->set('priority', rand(0, 10));

            $channel->basic_publish($msg, self::EXCHANGE_NAME);
            $io->info(sprintf(' [x] Sent %s', $i));
        }

        $channel->close();

        $io->info('Rabbitmq producer direct command');

        return Command::SUCCESS;
    }
}