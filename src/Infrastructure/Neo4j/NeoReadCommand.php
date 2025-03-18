<?php

declare(strict_types=1);

namespace App\Infrastructure\Neo4j;

use App\Infrastructure\Neo4j\Core\NeoConnectionInterface;
use Exception;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'neo4j/read',
    description: 'Read nodes from Neo4j database',
)]
class NeoReadCommand extends Command
{
    public function __construct(
        protected NeoConnectionInterface $client,
        ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->client->getClient()->readTransaction(static function (TransactionInterface $tsx) use($io) {
                $result = $tsx->run('MATCH (p:Person) RETURN p LIMIT 10');

                foreach ($result->getResults() as $record) {
                    $personNode = $record->get('p');
                    $io->success('Person node: ' . $personNode->getProperty('name') . " " . $personNode->getProperty('born'));
                }
            });
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Read nodes from Neo4j database');

        return Command::SUCCESS;
    }
}