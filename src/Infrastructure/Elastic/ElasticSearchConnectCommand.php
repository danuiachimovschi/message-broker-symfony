<?php

declare(strict_types=1);

namespace App\Infrastructure\Elastic;

use App\Infrastructure\Elastic\Core\ElasticSearchConnectionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'elastic-search:connect',
    description: 'ElasticSearchConnect command',
)]
class ElasticSearchConnectCommand extends Command
{
    public function __construct(
        protected ElasticSearchConnectionInterface $elasticSearchConnection,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create an index in Elasticsearch')
            ->setHelp('This command allows you to create an index in Elasticsearch');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $params = [
            'index' => 'my_custom_index',
            'body' => [
                'settings' => [
                    'number_of_shards' => 3,
                    'number_of_replicas' => 2
                ],
                'mappings' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text'
                        ],
                        'description' => [
                            'type' => 'text'
                        ],
                        'author' => [
                            'type' => 'text'
                        ],
                        'email' => [
                            'type' => 'text'
                        ],
                        'phone' => [
                            'type' => 'text'
                        ],
                        'age' => [
                            'type' => 'integer'
                        ],
                        'created_at' => [
                            'type' => 'date'
                        ],
                        'updated_at' => [
                            'type' => 'date'
                        ]
                    ]
                ]
            ]
        ];

        $this->elasticSearchConnection->getClient()->indices()->create($params);

        $io->success('Index created');

        return Command::SUCCESS;
    }
}
