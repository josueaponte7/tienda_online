<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;
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

    public function deleteUser(string $id): void
    {
        try {
            $user = $this->userService->getUserById($id);
            $this->userService->deleteUser($id);

            $user_data['user_id'] = $user->getId();
            $user_data['email'] = $user->getEmail();

            $date = new DateTime('now');

            $data = [
                'message' => 'Eliminar usuario:' . json_encode($user_data),
                'module' => 'User',
                'action' => 'DELETE',
                'event_date' => $date->format('d-m-Y H:i'),
                'user' => 'Admin',
                'timestamp' => date('c'),
            ];

            $this->elasticsearchService->index('auditoria-admin', $data);
            // Enviar una notificación a través de Redis
            $this->notificationService->sendNotification(
                'user-notifications',
                '¡Eliminar usuario registrado: ' . $user->getId() . '!',
            );

            $this->rabbitMQService->publishMessage('user-notifications', [
                'type' => 'rabbitmq',
                'message' => '¡Modificar usuario registrado: ' . $user->getId() . '!',
            ]);

            $this->loggerService->logInfo('Usuario eliminado  exitosamente.', [
                'id' => $user->getEmail(),
                'email' => $user->getEmail(),
                'timestamp' => date('c'),
            ]);

            return;
        } catch (Exception $e) {
            throw new Exception('Error en el registro del usuario: ' . $e->getMessage());
        }
    }
}

