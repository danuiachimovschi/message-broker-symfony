<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Producer;

use App\Infrastructure\Rabbitmq\RabbitmqConnectionInterface;
use League\Csv\Reader;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'rabbitmq/user-producer',
    description: 'Rabbitmq second consumer header command',
)]
class UserProducerCommand extends Command
{
    private const EXCHANGE_NAME = 'e.user';

    private const QUEUE_USERS = ['q.users.1', 'q.users.2', 'q.users.3'];

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

        $channel->exchange_declare(self::EXCHANGE_NAME, 'direct', false, true, false);

        foreach (self::QUEUE_USERS as $queue) {
            $channel->queue_declare($queue, false, true, false, false);
            $channel->queue_bind($queue, self::EXCHANGE_NAME, $queue);
        }


        $csv = Reader::createFromPath(__DIR__ . '/../../../../fixtures/users.csv', 'r');
        $csv->setHeaderOffset(0);
        $counter = 0;

        foreach ($csv as $record) {
            $queueIndex = $counter % 3;
            $msg = new AMQPMessage(json_encode($record));

            $channel->basic_publish($msg, self::EXCHANGE_NAME, self::QUEUE_USERS[$queueIndex]);
            $io->info(sprintf(' [x] Sent %s', $record['Name']));

            $counter++;
        }

        $channel->close();

        $io->info('Rabbitmq producer direct command');

        return Command::SUCCESS;
    }
}