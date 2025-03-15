<?php

declare(strict_types=1);

namespace App\Infrastructure\Elastic;

use App\Infrastructure\Elastic\Core\ElasticSearchConnectionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'elastic-search:pagination',
    description: 'ElasticSearchDocumentCommand command',
)]
class ElasticSearchPaginationCommand extends Command
{
    private const INDEX_NAME = 'my_custom_index';

    public function __construct(
        protected ElasticSearchConnectionInterface $elasticSearchConnection,
        ?string                                    $name = null
    )
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setDescription('Retrieve paginated results from Elasticsearch.')
            ->setHelp('This command demonstrates pagination in Elasticsearch using the elasticsearch/elasticsearch client.')
            ->addArgument('page', InputArgument::OPTIONAL, 'Page number', 1)
            ->addArgument('page_size', InputArgument::OPTIONAL, 'Number of results per page', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $page = (int) $input->getArgument('page');
        $pageSize = (int) $input->getArgument('page_size');

        $params = [
            'index' => self::INDEX_NAME,
            'body' => [
                'query' => [
                    'wildcard' => [
                        'title' => 'd*'
                    ]
                ],
                'size' => $pageSize,
                'from' => ($page - 1) * $pageSize
            ]
        ];

        $response = $this->elasticSearchConnection->getClient()->search($params);

        $output->writeln("Page {$page}:");
        foreach ($response['hits']['hits'] as $hit) {
            $output->writeln($hit['_source']['title']);
        }

        $totalHits = $response['hits']['total']['value'];
        $totalPages = ceil($totalHits / $pageSize);

        $output->writeln("Total results: {$totalHits}");
        $output->writeln("Total pages: {$totalPages}");

        return Command::SUCCESS;
    }
}