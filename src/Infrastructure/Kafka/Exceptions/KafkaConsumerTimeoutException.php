<?php

declare(strict_types=1);

namespace App\Infrastructure\Kafka\Exceptions;

use RdKafka\Exception;

class KafkaConsumerTimeoutException extends Exception
{
}
