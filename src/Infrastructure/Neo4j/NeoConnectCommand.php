<?php

declare(strict_types=1);

namespace App\Infrastructure\Neo4j;

use Laudis\Neo4j\ClientBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'neo4j/connect',
    description: 'Connect to Neo4j database',
)]
class NeoConnectCommand extends Command
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = ClientBuilder::create()
            ->withDriver('bolt', 'bolt://neo4j:strongpassword123@neo4j:7687')
            ->withDefaultDriver('bolt')
            ->build();

        $client->run('CREATE (n:Person {name: "John Doe"})');

        $io->success('Connected to Neo4j database');

        return Command::SUCCESS;
    }
}