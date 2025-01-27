<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\ElasticsearchService;
use App\Service\RedisService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    private ElasticsearchService $elasticsearchService;

    public function __construct(ElasticsearchService $elasticsearchService)
    {
        $this->elasticsearchService = $elasticsearchService;
    }

    #[Route('/api/user/login', name: 'api_user_login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        RedisService $redisService,
    ): JsonResponse {
        // Decodificar la solicitud JSON
        $data = json_decode($request->getContent(), true);

        // Validar los campos necesarios
        if (!isset($data['email'], $data['password'])) {
            $attempts = $redisService->incrementLoginAttempts($data['email']);
            if ($attempts > 0) {
                return new JsonResponse(['error' => 'Invalid credentials', 'attempts' => $attempts,], 401);
            }

            return new JsonResponse(['error' => 'Email and password are required.'], 400);
        }

        // Buscar al usuario por email
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user) {
            return new JsonResponse(['error' => 'Invalid credentials.'], 401);
        }

        // Verificar la contraseña
        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials.'], 401);
        }

        $data = [
            'user' => $user->getEmail(),
            'action' => 'login',
            'timestamp' => date('c'),
        ];

        //TODO: IMPORTANTE Enviar data a ELASTICSEARCH DESCOMENTAR DESPUES
        $this->elasticsearchService->index('logs', $data);

        // Generar el token JWT
        $token = $jwtManager->create($user);

        //REFACTOR: prueba de etiqueta refactor

        //TEST: hola
        $redisService->storeToken($user->getId(), $token);
        return new JsonResponse(['token' => $redisService->getToken($user->getId()),]);
    }
}
