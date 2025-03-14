<?php

declare(strict_types=1);

namespace App\Infrastructure\Elastic;

use Elastic\Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'elastic-search:search',
    description: 'ElasticSearch Search command',
)]
class ElasticSearchSearchCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = ClientBuilder::create()
            ->setHosts(['https://es01:9200'])
            ->setSSLVerification(false)
            ->setBasicAuthentication('elastic', 'changeme')
            ->build();


        $params = [
            'index' => 'my_custom_index',
            'body'  => [
                'query' => [
                    'match' => [
                        'title' => [
                            'query' => 'Sample Title 1',
                            'operator' => 'and'
                        ]
                    ]
                ]
            ]
        ];

        $response = $client->search($params);

        $body = $response->asArray();

        var_dump($body);

        return Command::SUCCESS;
    }
}