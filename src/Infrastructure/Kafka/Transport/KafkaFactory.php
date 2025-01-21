<?php

declare(strict_types=1);

namespace App\Infrastructure\Kafka\Transport;

use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;

class KafkaFactory
{
    public function createConsumer(array $kafkaConfig): KafkaConsumer
    {
        $conf = new Conf();

        foreach ($kafkaConfig as $key => $value) {
            if (array_key_exists($key, array_merge(KafkaOption::global(), KafkaOption::consumer()))) {
                if (!is_string($value)) {
                    // todo: warning
                    continue;
                }
                $conf->set($key, $value);
            }
        }

        return new KafkaConsumer($conf);
    }

    public function createProducer(array $kafkaConfig): Producer
    {
        $conf = new Conf();

        foreach ($kafkaConfig as $key => $value) {
            if (array_key_exists($key, array_merge(KafkaOption::global(), KafkaOption::producer()))) {
                if (!is_string($value)) {
                    // todo: warning
                    continue;
                }
                $conf->set($key, $value);
            }
        }

        return new Producer($conf);
    }
}
