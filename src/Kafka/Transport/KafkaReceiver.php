<?php

declare(strict_types=1);

namespace App\Kafka\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use RdKafka\Exception;

class KafkaReceiver implements ReceiverInterface
{
    private SerializerInterface $serializer;
    private Connection $connection;

    public function __construct(Connection $connection, SerializerInterface $serializer = null)
    {
        $this->connection = $connection;
        $this->serializer = $serializer ?? new PhpSerializer();
    }

    public function get(): iterable
    {
        yield from $this->getEnvelope();
    }

    public function ack(Envelope $envelope): void
    {
    }

    public function reject(Envelope $envelope): void
    {
    }

    private function getEnvelope(): iterable
    {
        try {
            $kafkaMessage = $this->connection->get();
        } catch (Exception $exception) {
            throw new TransportException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $kafkaMessage->err) {
            switch ($kafkaMessage->err) {
                case RD_KAFKA_RESP_ERR__PARTITION_EOF: // No more messages
                case RD_KAFKA_RESP_ERR__TIMED_OUT: // Timeout
                    return;
                default:
                    throw new TransportException($kafkaMessage->errstr(), $kafkaMessage->err);
            }
        }

        yield $this->serializer->decode([
            'body' => $kafkaMessage->payload,
            'headers' => $kafkaMessage->headers,
        ]);
    }
}
