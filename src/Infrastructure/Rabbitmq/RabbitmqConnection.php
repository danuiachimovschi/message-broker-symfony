<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitmqConnection implements RabbitmqConnectionInterface
{
    private AMQPStreamConnection $connection;

    public function __construct(AMQPStreamConnection $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }
}