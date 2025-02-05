<?php

declare(strict_types=1);

namespace App\Infrastructure\Rabbitmq;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitmqConnectionFactory
{
    private static ?AMQPStreamConnection $connection = null;

    public static function createConnection(): AMQPStreamConnection
    {
        if (self::$connection === null) {
            self::$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        }

        return self::$connection;
    }
}