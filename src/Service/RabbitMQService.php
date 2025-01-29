<?php

declare(strict_types=1);

namespace App\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQService
{
    private AMQPStreamConnection $connection;

    public function __construct(string $host, int $port, string $user, string $password)
    {
        $this->connection = new AMQPStreamConnection($host, $port, $user, $password);
    }

    public function getQueueInfo(string $queueName): array
    {
        $channel = $this->connection->channel();
        $queueInfo = $channel->queue_declare($queueName, true);
        dd($queueInfo);
        $channel->close();

        return [
            'message_count' => $queueInfo[1], // Cantidad de mensajes en la cola
            'consumer_count' => $queueInfo[2], // Cantidad de consumidores
        ];
    }
}
