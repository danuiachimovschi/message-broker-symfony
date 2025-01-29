<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\user\Entity\User;
use AvroSchema;
use Doctrine\ORM\EntityManagerInterface;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
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


        $schemaRegistryClient = new CachedRegistry(
            new PromisingRegistry(
                new Client(['base_uri' => 'schema-registry:8081'])
            ),
            new AvroObjectCacheAdapter()
        );

        $recordSerializer = new RecordSerializer(
            $schemaRegistryClient,
            [
                RecordSerializer::OPTION_REGISTER_MISSING_SCHEMAS => false,
                RecordSerializer::OPTION_REGISTER_MISSING_SUBJECTS => true,
            ]
        );

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

        while (true) {
            try {
                $message = $consumer->consume();

                $userData =  $recordSerializer->decodeMessage($message->getBody(), $avroSchema);

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
