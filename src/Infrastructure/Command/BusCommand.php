<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Infrastructure\Queue\Messenger\Message\OrderPaidMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'bus',
    description: 'Add a short description for your command',
)]
class BusCommand extends Command
{
    private readonly MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        parent::__construct();

        $this->messageBus = $messageBus;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->messageBus->dispatch(new OrderPaidMessage(rand(1, 100)));

        $io->success('Message dispatched');

        return Command::SUCCESS;
    }
}
