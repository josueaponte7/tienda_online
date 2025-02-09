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
use RuntimeException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * Service class for handling user registration.
 */
readonly class UserRegistrationService
{
    /**
     * Constructor for UserRegistrationService.
     *
     * @param UserService $userService
     * @param DocumentManager $documentManager
     * @param RedisNotificationService $notificationService
     * @param RabbitMQService $rabbitMQService
     * @param MessageBusInterface $bus
     * @param LoggerService $loggerService
     * @param ElasticsearchService $elasticsearchService
     * @param EntitySerializer $entitySerializer
     */
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

    /**
     * Registers a new user.
     *
     * @param RegisterUserDTO $dto Data transfer object containing user registration information.
     * @return User The registered user entity.
     * @throws ExceptionInterface
     * @throws Throwable
     *
     * SECTION: sendNotification: Referencia a la SECCIÓN de envio de notificación a redis
     * @ANCHOR: sendNotification
     */
    public function registerUser(RegisterUserDTO $dto): User
    {
        try {
            // Register the user
            $user = $this->userService->registerUser($dto);

            // Send an asynchronous email
            $this->bus->dispatch(
                new SendEmailMessage($dto->getEmail(), 'Bienvenido', 'Gracias por registrarte.'),
            );

            // Register the event in MongoDB
            $userRegister = new UserRegister($user->getId(), $user->getEmail());
            $this->documentManager->persist($userRegister);
            $this->documentManager->flush();

            // Log the registration event
            $this->loggerService->logInfo('Usuario registrado exitosamente.', [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'timestamp' => date('c'),
            ]);

            $data = [
                'message' => '¡Nuevo usuario registrado!',
                'details' => [
                    'action' => 'create',
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                ],
            ];

            // --------------------------
            // SECTION: sendNotification
            // Enviar notificación a redis para tiempo real
            // --------------------------
            //@ANCHOR: sendNotification
            // Send a notification through Redis
            $this->notificationService->sendNotification(
                'user-notifications',
                json_encode($data, JSON_THROW_ON_ERROR),
            );

            // Publish a message to RabbitMQ
            $this->rabbitMQService->publishMessage('user-notifications', [
                'type' => 'rabbitmq',
                'message' => $data,
            ]);

            // Index the event in Elasticsearch
            $date = new DateTime('now');
            $user_data['user_id'] = $user->getId();
            $user_data['email'] = $user->getEmail();
            $data = [
                'message' => 'Crear nuevo usuario:' . json_encode($user_data, JSON_THROW_ON_ERROR),
                'module' => 'User',
                'action' => 'REGISTER',
                'event_date' => $date->format('d-m-Y H:i'),
                'user' => 'No user',
                'timestamp' => date('c'),
            ];
            $this->elasticsearchService->index('auditoria-admin', $data);

            return $user;
        } catch (Exception $e) {
            throw new RuntimeException('Error en el registro del usuario: ' . $e->getMessage());
        }
    }
}
