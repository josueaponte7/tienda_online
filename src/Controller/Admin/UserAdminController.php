<?php

namespace App\Controller\Admin;

use App\DTO\RegisterUserDTO;
use App\Request\UserRegisterRequest;
use App\Service\UserService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

        if (!$user) {
            throw $this->createNotFoundException('Usuario no encontrado.');
        }

        $form = $this->createFormBuilder($user)
            ->add('email', EmailType::class, ['label' => 'Correo electrónico'])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Usuario' => 'ROLE_USER',
                    'Administrador' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'label' => 'Roles',
            ])
            ->add('Guardar', SubmitType::class, ['attr' => ['class' => 'btn btn-primary']])
            ->getForm();

        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();
            $password = '';
            $roles = $form->get('roles')->getData();

            $dto = new RegisterUserDTO($email, $password, $roles);
            $this->userService->updateUser($id, $dto); // Usa tu servicio para guardar cambios
            $this->addFlash('success', 'Usuario actualizado correctamente.');

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(string $id): Response
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            throw $this->createNotFoundException('Usuario no encontrado.');
        }

        $this->userService->deleteUser($user);
        $this->addFlash('success', 'Usuario eliminado correctamente.');

        return $this->redirectToRoute('admin_users');
    }
}
