<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\UserRegister;
use App\DTO\RegisterUserDTO;
use App\Message\SendEmailMessage;
use App\Request\UserRegisterRequest;
use App\Service\NotificationService;
use App\Service\UserService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private UserService $userService;
    private JWTTokenManagerInterface $jwtManager;
    private DocumentManager $documentManager;
    private NotificationService $notificationService;
    private MessageBusInterface $bus;

    public function __construct(
        UserService $userService,
        JWTTokenManagerInterface $jwtManager,
        DocumentManager $documentManager,
        NotificationService $notificationService,
        MessageBusInterface $bus,
    ) {
        $this->userService = $userService;
        $this->jwtManager = $jwtManager;
        $this->documentManager = $documentManager;
        $this->notificationService = $notificationService;
        $this->bus = $bus;
    }

    #[Route('/api/user/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $registerRequest = UserRegisterRequest::fromRequest($request);

        $dto = new RegisterUserDTO(
            $registerRequest->getEmail(),
            $registerRequest->getPassword(),
            $registerRequest->getRoles(),
        );

        try {
            $user = $this->userService->registerUser($dto);
            $this->bus->dispatch(
                new SendEmailMessage(
                    $user->getEmail(),
                    'Bienvenido',
                    'Gracias por registrarte.',
                ),
            );
            $user_register = new UserRegister($user->getId(), $user->getEmail());
            $this->documentManager->persist($user_register);
            $this->documentManager->flush();

            // Enviar notificación al canal de Redis
            $this->notificationService->sendNotification(
                'user-notifications',
                '¡Nuevo usuario registrado: ' . $user->getEmail() . '!',
            );

            return new JsonResponse(['message' => 'Usuario registrado con exito'], 201);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/user/edit/{id}', name: 'api_user_edit', methods: ['POST'])]
    public function edit(string $id, Request $request): JsonResponse
    {
        try {
            // Parsear el Request usando un DTO o un Request personalizado
            $editRequest = UserRegisterRequest::fromRequest($request);

            // Crear el DTO para editar el usuario
            $dto = new RegisterUserDTO(
                $editRequest->getEmail(),
                $editRequest->getPassword(),
                $editRequest->getRoles()
            );

            // Llamar al servicio de usuario para editar
            $this->userService->updateUser($id, $dto);

            return new JsonResponse(['message' => 'Usuario editado con éxito'], 200);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/user/delete/{id}', name: 'api_user_delete', methods: ['POST'])]
    public function delete(string $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);

            if (!$user) {
                throw $this->createNotFoundException('Usuario no encontrado.');
            }

            $this->userService->deleteUser($user);
            return new JsonResponse(['message' => 'Usuario eliminado con éxito'], 200);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
