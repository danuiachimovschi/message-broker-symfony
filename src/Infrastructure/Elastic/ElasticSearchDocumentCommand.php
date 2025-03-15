<?php

declare(strict_types=1);

namespace App\Infrastructure\Elastic;

use App\Infrastructure\Elastic\Core\ElasticSearchConnectionInterface;
use Faker\Factory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'elastic-search:document',
    description: 'ElasticSearchDocumentCommand command',
)]
class ElasticSearchDocumentCommand extends Command
{
    private const INDEX_NAME = 'my_custom_index';

    private const TOTAL_DOCS = 10_000;

    public function __construct(
        protected ElasticSearchConnectionInterface $elasticSearchConnection,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $params = ['body' => []];

        $faker = Factory::create();

        for ($i = 1; $i <= self::TOTAL_DOCS; $i++) {
            $createdAt = $faker->dateTimeBetween('-1 year', 'now')->format('c');
            $updatedAt = $faker->dateTimeBetween($createdAt, 'now')->format('c');

            $params['body'][] = [
                'index' => [
                    '_index' => self::INDEX_NAME,
                    '_id'    => $i
                ]
            ];
            $params['body'][] = [
                'title'       => $faker->sentence(),
                'description' => $faker->paragraph(),
                'author'      => $faker->name(),
                'email'       => $faker->unique()->email(),
                'phone'       => $faker->phoneNumber(),
                'age'        => $faker->numberBetween(1, 2),
                'created_at'  => $createdAt,
                'updated_at'  => $updatedAt
            ];

            if ($i % 1000 == 0) {
                $this->elasticSearchConnection->getClient()->bulk($params);
                $params = ['body' => []]; // Reset batch
                $io->success("Inserted $i documents...");
            }
        }

        if (!empty($params['body'])) {
            $this->elasticSearchConnection->getClient()->bulk($params);
            $io->success('Inserted final batch!');
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
