<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\UserRegister;
use App\DTO\RegisterUserDTO;
use App\Request\UserRegisterRequest;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Document\Log;
use Doctrine\ODM\MongoDB\DocumentManager;

class UserController extends AbstractController
{
    private UserService $userService;
    private JWTTokenManagerInterface $jwtManager;
    private DocumentManager $documentManager;

    public function __construct(UserService $userService, JWTTokenManagerInterface $jwtManager, DocumentManager $documentManager)
    {
        $this->userService = $userService;
        $this->jwtManager = $jwtManager;
        $this->documentManager = $documentManager;
    }

    #[Route('/api/user/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, DocumentManager $dm): JsonResponse
    {
        $registerRequest = new UserRegisterRequest($request);


        $dto = new RegisterUserDTO($registerRequest->getEmail(), $registerRequest->getPassword());

        try {
            $user = $this->userService->registerUser($dto);
            /*$log = new Log('NUEVA PRUEBA 1');
            $this->documentManager->persist($log);
            $this->documentManager->flush();*/

            $user_register = new UserRegister($user->getId(), $user->getEmail());
            $this->documentManager->persist($user_register);
            $this->documentManager->flush();

            return new JsonResponse(['message' => 'User registered successfully'], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
