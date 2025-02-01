<?php

namespace App\Controller\Admin;

use App\DTO\RegisterUserDTO;
use App\Request\Admin\UserRegisterRequest;
use App\Service\ElasticsearchService;
use App\Service\LoggerService;
use App\Service\UserRegistrationService;
use App\Service\UserService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserAdminController extends AbstractController
{
    public function __construct(
        private UserRegistrationService $userRegistrationService,
        private UserService $userService,
        private LoggerService $loggerService,
        private ElasticsearchService $elasticsearchService
    ) {
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function index(): Response
    {
        $users = $this->userService->getAllUsers();
        $data = [
            'messasge' => 'Acceso al administrador de usuarios',
            'action' => 'acceso',
            'timestamp' => date('c'),
        ];

        //TODO: IMPORTANTE Enviar data a ELASTICSEARCH DESCOMENTAR DESPUES
        // **Registrar el evento en Elasticsearch**
        $this->elasticsearchService->index('auditoria-admin', $data);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/create', name: 'admin_user_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
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
                $this->userRegistrationService->registerUser($dto);

                $this->addFlash('success', 'Usuario creado exitosamente.');
                return $this->redirectToRoute('admin_users');
            } catch (Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('admin/users/create.html.twig');
    }

    #[Route('/users/edit/{id}', name: 'user_edit')]
    public function edit(string $id, Request $request): Response
    {
        $user = $this->userService->getUserById($id);

        if ($request->isMethod('POST')) {
            $editRequest = UserRegisterRequest::fromRequest($request);

            $dto = new RegisterUserDTO(
                $editRequest->getEmail(),
                $editRequest->getPassword(),
                $editRequest->getRoles(),
            );

            $this->userService->updateUser($id, $dto);
            $this->addFlash('success', 'Usuario actualizado correctamente.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/users/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(string $id): Response
    {
        $this->userService->deleteUser($id);
        $this->addFlash('success', 'Usuario eliminado correctamente.');

        return $this->redirectToRoute('admin_users');
    }
}
