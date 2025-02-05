<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Producer;

use App\Infrastructure\Rabbitmq\RabbitmqConnection;
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
    private const EXCHANGE_NAME = 'e.direct';

    public function __construct(
        protected readonly RabbitmqConnection $rabbitmqConnection,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->rabbitmqConnection->getConnection();
        $channel = $connection->channel();

        $msg = new AMQPMessage(json_encode(['Hello World!']));

        $channel->basic_publish($msg, self::EXCHANGE_NAME, '');

        $channel->close();

        $io = new SymfonyStyle($input, $output);

        $io->info('Rabbitmq producer direct command');

        return Command::SUCCESS;
    }
}
