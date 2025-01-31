<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\UserRegister;
use App\DTO\RegisterUserDTO;
use App\Entity\User;
use App\Message\SendEmailMessage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class UserRegistrationService
{
    public function __construct(
        private UserService $userService,
        private DocumentManager $documentManager,
        private RedisNotificationService $notificationService,
        private RabbitMQService $rabbitMQService,
        private MessageBusInterface $bus,
        private LoggerService $loggerService,
        private ElasticsearchService $elasticsearchService
    ) {
    }

    public function registerUser(RegisterUserDTO $dto): User
    {
        try {

            // Registrar al usuario
            $user = $this->userService->registerUser($dto);

            // Enviar un email asincrónico
            $this->bus->dispatch(
                new SendEmailMessage($dto->getEmail(), 'Bienvenido', 'Gracias por registrarte.'),
            );

            // Registrar el evento en MongoDB
            $userRegister = new UserRegister($user->getId(), $user->getEmail());
            $this->documentManager->persist($userRegister);
            $this->documentManager->flush();

            $this->loggerService->logInfo('Usuario registrado exitosamente.', [
                'email' => $dto->getEmail(),
                'timestamp' => date('c'),
            ]);

            // Enviar una notificación a través de Redis
            $this->notificationService->sendNotification(
                'user-notifications',
                '¡Nuevo usuario registrado: ' . $user->getEmail() . '!',
            );

            $this->rabbitMQService->publishMessage('user-notifications', [
                'type' => 'rabbitmq',
                'message' => '¡Nuevo usuario registrado: ' . $user->getEmail() . '!'
            ]);

            $data = [
                'messasge' => 'Crear nuevo usuario:'.$user->getEmail(),
                'action' => 'create-user',
                'timestamp' => date('c'),
            ];

            //TODO: IMPORTANTE Enviar data a ELASTICSEARCH DESCOMENTAR DESPUES
            // **Registrar el evento en Elasticsearch**
            $this->elasticsearchService->index('auditoria-admin', $data);

            return $user;
        } catch (Exception $e) {
            throw new Exception('Error en el registro del usuario: ' . $e->getMessage());
        }
    }
}

