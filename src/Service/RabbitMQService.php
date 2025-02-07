<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use RuntimeException;

class RabbitMQService
{
    private AMQPStreamConnection $connection;
    private LoggerInterface $logger;

    public function __construct(string $host, int $port, string $user, string $password, LoggerInterface $logger)
    {
        $this->connection = new AMQPStreamConnection($host, $port, $user, $password);
        $this->logger = $logger;
    }

    public function getQueueInfo(string $queueName): array
    {
        $channel = $this->connection->channel();
        $queueInfo = $channel->queue_declare($queueName, true);
        $channel->close();

        return [
            'message_count' => $queueInfo[1], // Cantidad de mensajes en la cola
            'consumer_count' => $queueInfo[2], // Cantidad de consumidores
        ];
    }

    /**
     * Publicar un mensaje en una cola
     */
    public function publishMessage(string $queueName, array $message): void
    {
        try {
            $channel = $this->connection->channel();

            // Declarar la cola si no existe (durable=true para persistencia)
            $channel->queue_declare($queueName, false, true, false, false);

            // Crear el mensaje
            $msg = new AMQPMessage(json_encode($message, JSON_THROW_ON_ERROR), [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);

            // Publicar el mensaje
            $channel->basic_publish($msg, '', $queueName);
            $this->logger->info("Mensaje publicado en la cola '$queueName'", $message);

            $channel->close();
        } catch (AMQPExceptionInterface $e) {
            $this->logger->error("Error al publicar mensaje en RabbitMQ: " . $e->getMessage());
            throw new RuntimeException('Error al enviar el mensaje a RabbitMQ.');
        }
    }

    /**
     * Consumir mensajes de una cola
     */
    public function consumeMessages(string $queueName, callable $callback): void
    {
        try {
            $channel = $this->connection->channel();

            // Declarar la cola si no existe
            $channel->queue_declare($queueName, false, true, false, false);

            // Consumir mensajes de la cola
            $channel->basic_consume(
                $queueName,
                '',
                false,
                true,
                false,
                false,
                function (AMQPMessage $msg) use ($callback, $queueName) {
                    $data = json_decode($msg->body, true, 512, JSON_THROW_ON_ERROR);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $this->logger->info("Mensaje recibido de la cola '$queueName'", $data);
                        $callback($data);
                    } else {
                        $this->logger->warning("Mensaje malformado recibido de la cola '$queueName': " . $msg->body);
                    }
                },
            );

            // Esperar los mensajes en un bucle
            while ($channel->is_consuming()) {
                $channel->wait();  // Bloquea esperando mensajes
            }

            // Cerrar el canal después de terminar el consumo
            $channel->close();
        } catch (AMQPExceptionInterface $e) {
            $this->logger->error("Error al consumir mensajes de RabbitMQ: " . $e->getMessage());
            throw new RuntimeException('Error al consumir mensajes de RabbitMQ.');
        } finally {
            // Asegurar que la conexión se cierra
            if ($this->connection->isConnected()) {
                $this->connection->close();
            }
        }
    }

    public function consumeSingleMessage(string $queueName, callable $callback): void
    {
        try {
            $channel = $this->connection->channel();

            // Declarar la cola
            $channel->queue_declare($queueName, false, true, false, false);

            // Configura el consumidor para manejar un único mensaje
            $channel->basic_consume($queueName, '', false, true, false, false, function ($msg) use ($callback) {
                $data = json_decode($msg->body, true, 512, JSON_THROW_ON_ERROR);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $callback($data);
                }
            });

            // Esperar un único mensaje con timeout de 5 segundos
            $channel->wait(null, false, 5);

            // Cerrar el canal
            $channel->close();
        } catch (Exception $e) {
            $this->logger->error("Error al consumir mensaje: " . $e->getMessage());
        }
    }

    /**
     * Cerrar la conexión a RabbitMQ
     */
    public function closeConnection(): void
    {
        if ($this->connection->isConnected()) {
            $this->connection->close();
            $this->logger->info('Conexión a RabbitMQ cerrada.');
        }
    }
}
