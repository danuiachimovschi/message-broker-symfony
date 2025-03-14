<?php

declare(strict_types=1);

namespace App\Infrastructure\Elastic;

use App\Infrastructure\Elastic\Core\ElasticSearchConnectionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'elastic-search:document',
    description: 'ElasticSearchDocumentCommand command',
)]
class ElasticSearchDocumentCommand extends Command
{
    public function __construct(
        protected ElasticSearchConnectionInterface $elasticSearchConnection,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $params = ['body' => []];

        $totalDocuments = 10000;

        for ($i = 1; $i <= $totalDocuments; $i++) {
            $params['body'][] = [
                'index' => [
                    '_index' => 'my_custom_index',
                    '_id'    => $i
                ]
            ];
            $params['body'][] = [
                'title'       => "Sample Title $i",
                'description' => "This is a sample description for document $i.",
                'created_at'  => date('c') // ISO 8601 format
            ];

            // Send request every 1,000 documents to avoid memory overflow
            if ($i % 1000 == 0) {
                $this->elasticSearchConnection->getClient()->bulk($params);
                $params = ['body' => []]; // Reset batch
                echo "Inserted $i documents...\n";
            }
        }

        if (!empty($params['body'])) {
            $this->elasticSearchConnection->getClient()->bulk($params);
            echo "Inserted final batch!\n";
        }

        echo "10,000 documents inserted successfully!\n";

        return Command::SUCCESS;
    }
}
