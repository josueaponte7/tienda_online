<?php

namespace App\Controller\Admin;

use App\DTO\RegisterUserDTO;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserAdminController extends AbstractController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function index(): Response
    {
        $users = $this->userService->getAllUsers();
        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/create', name: 'admin_user_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $rolesString = $request->request->get('roles', 'ROLE_USER'); // Valor predeterminado si no existe
            $roles = array_map('trim', explode(',', $rolesString));
            try {
                $dto = new RegisterUserDTO($email, $password, $roles);
                $this->userService->registerUser($dto);

                $this->addFlash('success', 'Usuario creado exitosamente.');
                return $this->redirectToRoute('admin_users');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('admin/users/create.html.twig');
    }
}
