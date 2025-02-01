<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RegisterUserDTO;
use App\Entity\User;
use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;

readonly class UserUpdateService
{
    public function __construct(
        private UserService $userService,
        private DocumentManager $documentManager,
        private RedisNotificationService $notificationService,
        private RabbitMQService $rabbitMQService,
        private LoggerService $loggerService,
        private ElasticsearchService $elasticsearchService,
    ) {
    }

    public function updateUser(string $id, RegisterUserDTO $dto): User
    {
        try {
            $user_email_old = ($this->userService->getUserById($id))->getEmail();
            $user = $this->userService->updateUser($id, $dto);

            $user_data['user_id'] = $user->getId();
            if ($user_email_old !== $user->getEmail()) {
                $user_data['email_old'] = $user_email_old;
                $user_data['email_new'] = $user->getEmail();
            } else {
                $user_data['email'] = $user->getEmail();
            }
            $date = new DateTime('now');
            $data = [
                'message' => 'Editar usuario:' . json_encode($user_data),
                'module' => 'User',
                'action' => 'UPDATE',
                'event_date' => $date->format('d-m-Y H:i'),
                'user' => 'Admin',
                'timestamp' => date('c'),
            ];
            $this->elasticsearchService->index('auditoria-admin', $data);
            // Enviar una notificación a través de Redis
            $this->notificationService->sendNotification(
                'user-notifications',
                '¡Modificar usuario registrado: ' . $user->getEmail() . '!',
            );

            $this->rabbitMQService->publishMessage('user-notifications', [
                'type' => 'rabbitmq',
                'message' => '¡Modificar usuario registrado: ' . $user->getEmail() . '!',
            ]);

            $this->loggerService->logInfo('Usuario actualizado  exitosamente.', [
                'id' => $user->getEmail(),
                'email' => $user->getEmail(),
                'timestamp' => date('c'),
            ]);

            return $this->userService->getUserById($id);
        } catch (Exception $e) {
            throw new Exception('Error en el registro del usuario: ' . $e->getMessage());
        }
    }
}

