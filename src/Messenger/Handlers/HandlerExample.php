<?php

namespace App\Messenger\Handlers;

use App\Messenger\Message\OrderPaidMessage;
use App\Rabbit\Messages\ExampleMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class HandlerExample
{
    public function __invoke(OrderPaidMessage $message): void
    {
        echo 'Received message: ' . $message->getContent();
    }
}