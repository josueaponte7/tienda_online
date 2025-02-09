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
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * Controlador para registrar usuarios a través de la API.
 *
 * Este controlador recibe solicitudes de registro de usuarios, valida los datos,
 * y utiliza servicios para manejar el proceso de creación de usuarios.
 *
 * @property-read LoggerService $loggerService              Servicio para registrar logs de la aplicación.
 * @property-read UserRegistrationService $userRegistrationService  Servicio para manejar el registro de usuarios.
 * @property-read ElasticsearchService $elasticsearchService Servicio para búsquedas y almacenamiento en Elasticsearch.
 */
class UserRegisterController extends AbstractController
{
    /**
     * Constructor del controlador.
     *
     * @param LoggerService $loggerService Servicio para manejo de logs.
     * @param UserRegistrationService $userRegistrationService Servicio para el registro de usuarios.
     * @param ElasticsearchService $elasticsearchService Servicio para búsqueda en Elasticsearch.
     */
    public function __construct(
        private readonly LoggerService $loggerService,
        private readonly UserRegistrationService $userRegistrationService,
        private readonly ElasticsearchService $elasticsearchService,
    ) {
    }

    /**
     * Maneja la solicitud POST de registro de usuario.
     *
     * @param Request $request La solicitud HTTP.
     * @return JsonResponse Respuesta JSON con el resultado del registro.
     *
     * ANCLA: $this->userRegistrationService->registerUser($dto);
     * @throws ExceptionInterface
     * @throws Throwable
     */
    #[Route('/api/user/register', name: 'api_register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            // Extraer datos de la solicitud
            $registerRequest = UserRegisterRequest::fromRequest($request);

            // Crear un DTO con los datos del usuario
            $dto = new RegisterUserDTO(
                $registerRequest->getEmail(),
                $registerRequest->getPassword(),
                $registerRequest->getRoles(),
            );

            /**
             * Llama al método `registerUser` del servicio `UserRegistrationService`.
             * @see UserRegistrationService::registerUser
             */
            $this->userRegistrationService->registerUser($dto);

            // Respuesta exitosa
            return JsonResponseService::success(['message' => 'Usuario registrado con éxito'], 201);
        } catch (Exception $e) {
            // Manejar errores y registrar el evento
            $this->loggerService->logError('Error al registrar usuario.', [
                'error' => $e->getMessage(),
            ]);

            return JsonResponseService::error($e->getMessage());
        }
    }
}
