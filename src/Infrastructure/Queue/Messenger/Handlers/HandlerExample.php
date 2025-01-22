<?php

namespace App\Infrastructure\Queue\Messenger\Handlers;

use App\Infrastructure\Queue\Messenger\Message\OrderPaidMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class HandlerExample
{
    public function __invoke(OrderPaidMessage $message): void
    {
        echo 'Received message: '.$message->getContent();
    }
}
