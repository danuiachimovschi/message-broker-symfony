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
    name: 'rabbitmq/producer-topic',
    description: 'Rabbitmq producer topic command',
)]
class TopicProducerCommand  extends Command
{
    private const EXCHANGE_NAME = 'e.topic';

    public function __construct(
        protected readonly RabbitmqConnection $rabbitmqConnection,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connection = $this->rabbitmqConnection->getConnection();
        $channel = $connection->channel();
        $channel->exchange_declare(self::EXCHANGE_NAME, 'topic', false, true, false);

        foreach (range(1, 1000) as $i) {
            $msg = new AMQPMessage(json_encode(['Hello World! ' . $i]));
            $channel->basic_publish($msg, self::EXCHANGE_NAME, "events.$i");
            $io->info(sprintf(' [x] Sent %s', $i));
        }

        $channel->close();

        $io->info('Rabbitmq producer direct command');

        return Command::SUCCESS;
    }
}