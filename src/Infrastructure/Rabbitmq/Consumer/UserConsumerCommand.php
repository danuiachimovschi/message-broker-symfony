<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq\Consumer;

use App\Domain\user\Entity\User;
use App\Infrastructure\Rabbitmq\RabbitmqConnectionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'rabbitmq/user-consumer',
    description: 'Rabbitmq second consumer topic command',
)]
class UserConsumerCommand extends Command
{
    private const EXCHANGE_NAME = 'e.user';

    private const QUEUE_USERS = ['q.users.1', 'q.users.2', 'q.users.3'];

    public function __construct(
        protected readonly RabbitmqConnectionInterface $rabbitmqConnection,
        private EntityManagerInterface $entityManager,
        ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('queue_name', InputArgument::REQUIRED, 'Queue name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!in_array($input->getArgument('queue_name'), self::QUEUE_USERS)) {
            $io->error('Queue name not found');
            return Command::FAILURE;
        }

        $connection = $this->rabbitmqConnection->getConnection();
        $channel = $connection->channel();

        $channel->exchange_declare(self::EXCHANGE_NAME, 'direct', false, true, false);

        $channel->queue_declare($input->getArgument('queue_name'), false, true, false, false);
        $channel->queue_bind($input->getArgument('queue_name'), self::EXCHANGE_NAME, $input->getArgument('queue_name'));

        $callback = function($msg) use($io) {

            $userData = json_decode($msg->body, true);
            $user = new User();
            $user->setName($userData['Name']);
            $user->setSurname($userData['Surname']);
            $user->setEmail($userData['Email']);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success('User created: ' . $user->getName() . ' ' . $user->getSurname() . ' ' . $user->getEmail());
        };

        $channel->basic_consume($input->getArgument('queue_name'), '', false, true, false, false, $callback);

        /** @phpstan-ignore while.alwaysTrue */
        while (true) {
            $channel->wait();
        }
    }
}