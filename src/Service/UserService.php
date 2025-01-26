<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RegisterUserDTO;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepositoryInterface;
use App\VO\Email;
use App\VO\Password;
use Exception;

class UserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerUser(RegisterUserDTO $dto): User
    {
        $email = new Email($dto->email);
        $roles = $dto->roles;

        if ($this->userRepository->existsByEmail($email->getValue())) {
            throw new Exception('User already exists.');
        }

        $password = new Password($dto->password);
        $user = UserFactory::create($email, $password, $roles);
        $this->userRepository->save($user);
        return $this->userRepository->findByEmail($dto->email);
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAllUsers();
    }

    public function getUserById(string $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function updateUser(string $id, RegisterUserDTO $dto): void
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new Exception('Usuario no encontrado.');
        }

        // Editar los datos del usuario
        $user->setEmail($dto->getEmail())
            ->setRoles(
                !empty($dto->getRoles()) ? $dto->getRoles() : ['ROLE_USER'],
            ); // Asignar roles predeterminados si están vacíos

        // Hashear la nueva contraseña si es necesario
        if ($dto->getPassword() || strlen($dto->getPassword()) > 0) {
            $hashedPassword = (new Password($dto->getPassword()))->getValue();
            $user->setPassword($hashedPassword);
        }

        // Guardar cambios en el repositorio
        $this->userRepository->save($user);
    }

    public function deleteUser(User $user): void
    {
        $this->userRepository->delete($user);
    }
}
