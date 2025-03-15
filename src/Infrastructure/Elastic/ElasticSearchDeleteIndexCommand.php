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
    name: 'elastic-search:delete-index',
    description: 'ElasticSearchDocumentCommand command',
)]
class ElasticSearchDeleteIndexCommand extends Command
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
            ->setDescription('Delete an index in Elasticsearch')
            ->setHelp('This command allows you to delete an index in Elasticsearch');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $params = [
            'index' => self::INDEX_NAME,
        ];

        try {
            $this->elasticSearchConnection->getClient()->indices()->delete($params);

            $io->success('Index deleted');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}