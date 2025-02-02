<?php

namespace App\Infrastructure\Avro;

use App\Infrastructure\Avro\Interfaces\SchemaRegistryClientInterface;
use AvroSchema;
use FlixTech\SchemaRegistryApi\AsynchronousRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use const PATHINFO_FILENAME;

#[AsCommand(
    name: 'avro-schema:register',
    description: 'Register Avro schema in schema registry',
)]
class AvroSchemaRegisterCommand extends Command
{
    protected AsynchronousRegistry $registry;

    public function __construct(
        private SchemaRegistryClientInterface $schemaRegistryClient,
        ?string $name = null)
    {
        $this->registry = $this->schemaRegistryClient->initPromisingRegistry();
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $files = glob(dirname(__DIR__, 3) . '/schema/avro/*.avsc');

        foreach ($files as $file) {
            $fileContent = file_get_contents($file);

            $schema = AvroSchema::parse($fileContent);

            $this->registry->register($this->getNameOfSchema($file), $schema)
                ->then(
                    function () use ($io, $file) {
                        $io->success(sprintf('Schema %s registered', $file));
                    },
                    function (Throwable $e) use ($io, $file) {
                        $io->error(sprintf('Schema %s not registered: %s', $file, $e->getMessage()));
                    }
                )->wait();
        }

        $io->info('Schema registration finished');

        return Command::SUCCESS;
    }

    protected function getNameOfSchema(string $file): string
    {
        return ucfirst(pathinfo($file, PATHINFO_FILENAME));
    }
}
