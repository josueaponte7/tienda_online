<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProtectedController extends AbstractController
{
    #[Route('/api/protected', name: 'api_protected', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Welcome to the protected route!',
            'user' => $this->getUser()->getUserIdentifier(),
        ]);
    }
}
