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
    name: 'neo4j/connect',
    description: 'Connect to Neo4j database',
)]
class NeoConnectCommand extends Command
{
    public function __construct(
        protected NeoConnectionInterface $client,
        ?string $name = null
    )
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->client->getClient()->writeTransaction(static function (TransactionInterface $tsx) {
                $tsx->runStatements([
                    Statement::create(
                        'MERGE (TomH:Person {name: $name, tmdbID: $tmdbID, born: $born})',
                        [
                            'name' => 'Tom Hanks',
                            'tmdbID' => 31,
                            'born' => '1956-07-09'
                        ]
                    ),
                    Statement::create(
                        'MERGE (MegR:Person {name: $name, tmdbID: $tmdbID, born: $born})',
                        [
                            'name' => 'Meg Ryan',
                            'tmdbID' => 5344,
                            'born' => '1961-11-19'
                        ]
                    ),
                    Statement::create(
                        'MERGE (DannyD:Person {name: $name, tmdbID: $tmdbID, born: $born})',
                        [
                            'name' => 'Danny DeVito',
                            'tmdbID' => 518,
                            'born' => '1944-11-17'
                        ]
                    ),
                    Statement::create(
                        'MERGE (JackN:Person {name: $name, tmdbID: $tmdbID, born: $born})',
                        [
                            'name' => 'Jack Nicholson',
                            'tmdbID' => 514,
                            'born' => '1937-04-22'
                        ]
                    ),
                ]);

                $tsx->runStatements([
                    Statement::create(
                        'MERGE (Apollo13:Movie {title: $title, tmdbID: $tmdbID, released: $released, imdbRating: $imdbRating, genres: $genres})',
                        [
                            'title' => 'Apollo 13',
                            'tmdbID' => 568,
                            'released' => '1995-06-30',
                            'imdbRating' => 7.6,
                            'genres' => ['Drama', 'Adventure', 'IMAX']
                        ]
                    ),
                    Statement::create(
                        'MERGE (SleeplessInSeattle:Movie {title: $title, tmdbID: $tmdbID, released: $released, imdbRating: $imdbRating, genres: $genres})',
                        [
                            'title' => 'Sleepless in Seattle',
                            'tmdbID' => 858,
                            'released' => '1993-06-25',
                            'imdbRating' => 6.8,
                            'genres' => ['Comedy', 'Drama', 'Romance']
                        ]
                    ),
                    Statement::create(
                        'MERGE (Hoffa:Movie {title: $title, tmdbID: $tmdbID, released: $released, imdbRating: $imdbRating, genres: $genres})',
                        [
                            'title' => 'Hoffa',
                            'tmdbID' => 10410,
                            'released' => '1992-12-25',
                            'imdbRating' => 6.6,
                            'genres' => ['Crime', 'Drama']
                        ]
                    ),
                ]);
            });
        } catch (Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Connected to Neo4j database');

        return Command::SUCCESS;
    }
}