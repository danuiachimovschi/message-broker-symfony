<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\user\Entity\User;
use App\Infrastructure\Avro\Interfaces\SchemaRegistryClientInterface;
use AvroSchema;
use Doctrine\ORM\EntityManagerInterface;
use Jobcloud\Kafka\Consumer\KafkaConsumerBuilder;
use Jobcloud\Kafka\Exception\KafkaConsumerConsumeException;
use Jobcloud\Kafka\Exception\KafkaConsumerEndOfPartitionException;
use Jobcloud\Kafka\Exception\KafkaConsumerTimeoutException;
use Jobcloud\Kafka\Message\Decoder\AvroDecoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function __construct(
        private EntityManagerInterface $entityManager,
        protected readonly SchemaRegistryClientInterface $schemaRegistryClient,
        string $name = null
    ) {
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

        $schema = <<<'JSON'
        {
            "type": "record",
            "name": "User",
            "fields": [
                {"name": "name", "type": "string"},
                {"name": "surname", "type": "string"},
                {"name": "email", "type": "string"}
            ]
        }
        JSON;

        $avroSchema = AvroSchema::parse($schema);

        $consumer = KafkaConsumerBuilder::create()
            ->withAdditionalConfig(
                [
                    'enable.auto.commit' => false,
                ]
            )
            ->withDecoder($this->schemaRegistryClient->getDecoder())
            ->withAdditionalBroker('kafka:9092')
            ->withConsumerGroup('testGroup')
            ->withAdditionalSubscription('users')
            ->build();

        $consumer->subscribe();

        while (true) {
            try {
                $message = $consumer->consume();

                $userData = $this->schemaRegistryClient->getRecordSerializer()->decodeMessage($message->getBody(), $avroSchema);

                $io->success('Message received: '. $userData['name']);

                $user = new User();
                $user->setName($userData['name']);
                $user->setSurname($userData['surname']);
                $user->setEmail($userData['email']);

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
