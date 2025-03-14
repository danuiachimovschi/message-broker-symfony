<?php

declare(strict_types=1);

namespace App\Infrastructure\Elastic;

use App\Infrastructure\Elastic\Core\ElasticSearchConnectionInterface;
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
    public function __construct(
        protected ElasticSearchConnectionInterface $elasticSearchConnection,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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

        $response = $this->elasticSearchConnection->getClient()->search($params);

        $body = $response->asArray();

        var_dump($body);

        return Command::SUCCESS;
    }
}