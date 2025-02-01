<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;

readonly class UserDeleteService
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

    public function delete(string $id): User
    {
        try {
            $user = $this->userService->getUserById($id);
            $this->userService->deleteUser($id);

            $user_data['user_id'] = $user->getId();
            $user_data['emai'] = $user->getEmail();

            $data = [
                'message' => 'Editar nuevo usuario:' . json_encode($user_data),
                'action' => 'user',
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

