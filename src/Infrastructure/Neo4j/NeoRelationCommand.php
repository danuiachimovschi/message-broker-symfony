<?php

declare(strict_types=1);

namespace App\Infrastructure\Neo4j;

use App\Infrastructure\Neo4j\Core\NeoConnectionInterface;
use Exception;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Databags\Statement;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'neo4j/relation',
    description: 'Create relation between nodes',
)]
class NeoRelationCommand extends Command
{
    public function __construct(
        protected NeoConnectionInterface $client,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->client->getClient()->writeTransaction(static function (TransactionInterface $tsx) {
                $tsx->runStatements([
                    Statement::create(
                        'MATCH (TomH:Person {name: $name1}), (Apollo13:Movie {title: $title1}) ' .
                        'MERGE (TomH)-[:ACTED_IN]->(Apollo13)',
                        [
                            'name1' => 'Tom Hanks',
                            'title1' => 'Apollo 13'
                        ]
                    ),

                    Statement::create(
                        'MATCH (MegR:Person {name: $name2}), (SleeplessInSeattle:Movie {title: $title2}) ' .
                        'MERGE (MegR)-[:ACTED_IN]->(SleeplessInSeattle)',
                        [
                            'name2' => 'Meg Ryan',
                            'title2' => 'Sleepless in Seattle'
                        ]
                    ),

                    Statement::create(
                        'MATCH (DannyD:Person {name: $name3}), (Hoffa:Movie {title: $title3}) ' .
                        'MERGE (DannyD)-[:ACTED_IN]->(Hoffa)',
                        [
                            'name3' => 'Danny DeVito',
                            'title3' => 'Hoffa'
                        ]
                    ),

                    Statement::create(
                        'MATCH (JackN:Person {name: $name4}), (Apollo13:Movie {title: $title4}) ' .
                        'MERGE (JackN)-[:ACTED_IN]->(Apollo13)',
                        [
                            'name4' => 'Jack Nicholson',
                            'title4' => 'Apollo 13'
                        ]
                    ),
                ]);
            });
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Created relation between nodes');

        return Command::SUCCESS;
    }
}