<?php

declare(strict_types=1);

namespace App\Infrastructure\Streams;

use Exception;
use React\EventLoop\Loop;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app/stream-client',
    description: 'Start a TCP stream client',
)]
class StreamClientCommand extends Command
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $loop = Loop::get();
        $connector = new Connector($loop);

        $serverAddress = 'tcp://localhost:8080';

        $connector->connect($serverAddress)->then(
            static function (ConnectionInterface $connection) use ($io) {
                $io->info('Connected to the TCP stream server.');

                $connection->on('data', function ($data) use ($io) {
                    $data = trim($data);
                    if (!empty($data)) {
                        $io->writeln("Received: " . $data);
                    }
                });

                $connection->on('error', function ($error) use ($io) {
                    $io->error("Stream error: " . $error->getMessage());
                });

                $connection->on('close', function () use ($io) {
                    $io->warning("Connection closed.");
                });
            },
            static function (Exception $e) use ($io) {
                $io->error("Connection failed: " . $e->getMessage());
            }
        );

        $loop->run();

        return Command::SUCCESS;
    }
}
