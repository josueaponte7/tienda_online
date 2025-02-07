<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\DTO\RegisterUserDTO;
use App\Request\Api\UserRegisterRequest;
use App\Service\ElasticsearchService;
use App\Service\JsonResponseService;
use App\Service\LoggerService;
use App\Service\UserRegistrationService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserRegisterController extends AbstractController
{
    public function __construct(
        private readonly LoggerService $loggerService,
        private readonly UserRegistrationService $userRegistrationService,
        private readonly ElasticsearchService $elasticsearchService,
    ) {
    }

    #[Route('/api/user/register', name: 'api_register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $registerRequest = UserRegisterRequest::fromRequest($request);

            $dto = new RegisterUserDTO(
                $registerRequest->getEmail(),
                $registerRequest->getPassword(),
                $registerRequest->getRoles(),
            );

            // Usar el servicio para manejar el registro
            $this->userRegistrationService->registerUser($dto);

            return JsonResponseService::success(['message' => 'Usuario registrado con éxito'], 201);
        } catch (Exception $e) {
            $this->loggerService->logError('Error al registrar usuario.', [
                'error' => $e->getMessage(),
            ]);
            return JsonResponseService::error($e->getMessage());
        }
    }
}
