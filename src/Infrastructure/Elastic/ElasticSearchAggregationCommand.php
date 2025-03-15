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
    name: 'elastic-search:aggregate',
    description: 'ElasticSearchDocumentCommand command',
)]
class ElasticSearchAggregationCommand extends Command
{
    private const INDEX_NAME = 'my_custom_index';

    public function __construct(
        protected ElasticSearchConnectionInterface $elasticSearchConnection,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Perform aggregation on Elasticsearch index')
            ->setHelp('This command allows you to perform an aggregation query on Elasticsearch');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $params = [
            'index' => self::INDEX_NAME,
            'body'  => [
                'aggs' => [
                    'average_aged' => [
                        'avg' => [
                            'field' => 'age'
                        ]
                    ]
                ]
            ],
            'refresh' => true
        ];

        try {
            $response = $this->elasticSearchConnection->getClient()->search($params);

            $averagePrice = $response['aggregations']['average_aged']['value'];

            $io->success('Average price: ' . round($averagePrice));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}