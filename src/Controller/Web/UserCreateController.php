<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\DTO\RegisterUserDTO;
use App\Request\Admin\UserRegisterRequest;
use App\Service\UserRegistrationService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserCreateController extends AbstractController
{
    #[Route('/user/create', name: 'user_create', methods: ['GET', 'POST'])]
    public function create(Request $request, UserRegistrationService $userRegistrationService): Response
    {
        if ($request->isMethod('POST')) {
            $registerRequest = UserRegisterRequest::fromRequest($request);

            try {
                $dto = new RegisterUserDTO(
                    $registerRequest->getEmail(),
                    $registerRequest->getPassword(),
                    $registerRequest->getRoles(),
                );

                // Usar el servicio para manejar el registro
                $userRegistrationService->registerUser($dto);

                $this->addFlash('success', 'Usuario creado exitosamente.');
                return $this->redirectToRoute('login');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('admin/users/create.html.twig');
    }
}
