<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq;

use PhpAmqpLib\Connection\AMQPStreamConnection;

interface RabbitmqConnectionInterface
{
    public function getConnection(): AMQPStreamConnection;
}