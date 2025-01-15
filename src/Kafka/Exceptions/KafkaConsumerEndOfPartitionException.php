<?php

declare(strict_types=1);

namespace App\Kafka\Exceptions;

use RdKafka\Exception;

class KafkaConsumerEndOfPartitionException extends Exception
{
}
