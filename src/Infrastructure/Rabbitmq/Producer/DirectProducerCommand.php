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
    name: 'rabbitmq/producer-direct',
    description: 'Rabbitmq producer direct command',
)]
class DirectProducerCommand extends Command
{
    public function __construct(
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('daniel', false, false, false, false);

        $msg = new AMQPMessage(json_encode(['Hello World!']));

        $channel->basic_publish($msg, '', 'daniel');

        $channel->close();
        $connection->close();

        $io = new SymfonyStyle($input, $output);

        $io->info('Rabbitmq producer direct command');

        return Command::SUCCESS;
    }
}
