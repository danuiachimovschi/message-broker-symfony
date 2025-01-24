<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\user\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Jobcloud\Kafka\Consumer\KafkaConsumerBuilder;
use Jobcloud\Kafka\Exception\KafkaConsumerConsumeException;
use Jobcloud\Kafka\Exception\KafkaConsumerEndOfPartitionException;
use Jobcloud\Kafka\Exception\KafkaConsumerTimeoutException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'user/consumer',
    description: 'User consumer command',
)]
class UserConsumerCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, string $name = null)
    {
        $this->entityManager = $entityManager;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('partition_id', InputArgument::REQUIRED, 'Partition ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $consumer = KafkaConsumerBuilder::create()
            ->withAdditionalConfig(
                [
                    'enable.auto.commit' => false
                ]
            )
            ->withAdditionalBroker('kafka:9092')
            ->withConsumerGroup('testGroup')
            ->withAdditionalSubscription('users')
            ->build();

        $consumer->subscribe();

        while (true) {
            try {
                $message = $consumer->consume();
                $userData = json_decode($message->getBody(), true);
                $io->success('Message received: '. $userData['Name']);

                $user = new User();
                $user->setName($userData['Name']);
                $user->setSurname($userData['Surname']);
                $user->setEmail($userData['Email']);

                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $consumer->commit($message);
            } catch (KafkaConsumerTimeoutException|KafkaConsumerEndOfPartitionException) {
            } catch (KafkaConsumerConsumeException) {
                return Command::FAILURE;
            }
        }
    }
}
