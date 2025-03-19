<?php

declare(strict_types=1);

namespace App\Infrastructure\Streams;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app/stream',
    description: 'Start a stream server',
)]
class StreamServerCommand extends Command
{
    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $loop = Loop::get();

        $server = new SocketServer('0.0.0.0:8080', [], $loop);

        $server->on('connection', function (ConnectionInterface $connection) use ($io, $loop) {
            $io->info('Client connected.');

            $loop->addPeriodicTimer(1, function () use ($connection) {
                $data = json_encode([
                    'message' => 'Hello, TCP Stream!',
                    'time' => date('H:i:s')
                ]);
                $connection->write("data: $data\n\n");
            });

            $connection->on('close', function () use ($io) {
                $io->warning('Client disconnected.');
            });

            $connection->on('error', function ($error) use ($io) {
                $io->error("Stream error: " . $error->getMessage());
            });
        });

        $io->success('TCP stream server running on tcp://0.0.0.0:8080');

        $loop->run();

        return Command::SUCCESS;
    }
}