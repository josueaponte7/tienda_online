<?php

declare(strict_types=1);

namespace App\Service;

use App\Document\UserRegister;
use App\DTO\RegisterUserDTO;
use App\Entity\User;
use App\Message\SendEmailMessage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Symfony\Component\Messenger\MessageBusInterface;

class UserRegistrationService
{
    public function __construct(
        private UserService $userService,
        private DocumentManager $documentManager,
        private NotificationService $notificationService,
        private MessageBusInterface $bus,
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

            // Enviar una notificación a través de Redis
            $this->notificationService->sendNotification(
                'user-notifications',
                '¡Nuevo usuario registrado: ' . $user->getEmail() . '!',
            );

            return $user;
        } catch (Exception $e) {
            throw new Exception('Error en el registro del usuario: ' . $e->getMessage());
        }
    }
}

