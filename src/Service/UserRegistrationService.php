<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\UserRegister;
use App\DTO\RegisterUserDTO;
use App\Entity\User;
use App\Message\SendEmailMessage;
use App\Serializer\EntitySerializer;
use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
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
        private ElasticsearchService $elasticsearchService,
        private EntitySerializer $entitySerializer,
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
                'id' => $user->getId(),
                'email' => $user->getEmail(),
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
            $date = new DateTime('now');
            $user_data['user_id'] = $user->getId();
            $user_data['email'] = $user->getEmail();
            $data = [
                'message' => 'Crear nuevo usuario:' . json_encode($user_data),
                'module' => 'User',
                'action' => 'REGISTER',
                'event_date' => $date->format('d-m-Y H:i'),
                'user' => 'No user',
                'timestamp' => date('c'),
            ];
            $this->elasticsearchService->index('auditoria-admin', $data);

            return $user;
        } catch (Exception $e) {
            throw new Exception('Error en el registro del usuario: ' . $e->getMessage());
        }
    }
}

