<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitmqConnection implements RabbitmqConnectionInterface
{
    private AMQPStreamConnection $connection;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
    }

    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    private function __clone(): void
    {
    }

    public function __wakeup(): void
    {
    }

    public function __destruct()
    {
    }
}