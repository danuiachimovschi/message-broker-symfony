<?php

declare(strict_types=1);

namespace App\Infrastructure\Elastic;

use App\Infrastructure\Elastic\Core\ElasticSearchConnectionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
                    'wildcard' => [
                        'title' => 't*'
                    ]
                ]
            ]
        ];

        $response = $this->elasticSearchConnection->getClient()->search($params);

        $body = $response->asArray();


        return Command::SUCCESS;
    }
}