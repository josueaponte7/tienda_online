<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\RegisterUserDTO;
use App\Request\Api\UserRegisterRequest;
use App\Service\ElasticsearchService;
use App\Service\JsonResponseService;
use App\Service\LoggerService;
use App\Service\UserDeleteService;
use App\Service\UserRegistrationService;
use App\Service\UserService;
use App\Service\UserUpdateService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private LoggerService $loggerService,
        private UserService $userService,
        private ElasticsearchService $elasticsearchService,
    ) {}

    #[Route('/api/user/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserRegistrationService $userRegistrationService): JsonResponse
    {
        try {
            $registerRequest = UserRegisterRequest::fromRequest($request);

            $dto = new RegisterUserDTO(
                $registerRequest->getEmail(),
                $registerRequest->getPassword(),
                $registerRequest->getRoles()
            );

            // Usar el servicio para manejar el registro
            $userRegistrationService->registerUser($dto);

            return JsonResponseService::success(['message' => 'Usuario registrado con éxito'], 201);
        } catch (Exception $e) {
            $this->loggerService->logError('Error al registrar usuario.', [
                'error' => $e->getMessage(),
            ]);
            return JsonResponseService::error($e->getMessage());
        }
    }

    #[Route('/api/user/edit/{id}', name: 'api_user_edit', methods: ['POST'])]
    public function edit(string $id, Request $request, UserUpdateService $userUpdateService): JsonResponse
    {
        try {
            $editRequest = UserRegisterRequest::fromRequest($request);

            $dto = new RegisterUserDTO(
                $editRequest->getEmail(),
                $editRequest->getPassword(),
                $editRequest->getRoles()
            );

            $userUpdateService->updateUser($id, $dto);

            return JsonResponseService::success(['message' => 'Usuario editado con éxito']);
        } catch (Exception $e) {
            return JsonResponseService::error($e->getMessage());
        }
    }

    #[Route('/api/user/delete/{id}', name: 'api_user_delete', methods: ['POST'])]
    public function delete(string $id, UserDeleteService $userDeleteService): JsonResponse
    {
        $userDeleteService->deleteUser($id);
        return JsonResponseService::success(['message' => 'Usuario eliminado con éxito']);
    }
}
