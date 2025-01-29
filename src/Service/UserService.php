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
use Psr\Log\LoggerInterface;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function registerUser(RegisterUserDTO $dto): User
    {
        $this->logger->info("Intentando registrar usuario: {$dto->getEmail()}");
        $email = new Email($dto->email);

        if ($this->userRepository->existsByEmail($email->getValue())) {
            $this->logger->error("El usuario ya existe: {$dto->getEmail()}");
            throw new Exception('User already exists.');
        }

        $password = new Password($dto->password);
        $user = UserFactory::create($email, $password, $dto->roles->getValue());
        $this->userRepository->save($user);

        return $this->userRepository->findByEmail($dto->email);
    }

    public function updateUser(string $id, RegisterUserDTO $dto): void
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new Exception('Usuario no encontrado.');
        }

        $email = new Email($dto->getEmail());
        $password = $dto->getPassword() ? new Password($dto->getPassword()) : null;
        $roles = !empty($dto->getRoles()->getValue()) ? $dto->getRoles()->getValue() : null;

        // Usamos la fábrica para actualizar el usuario
        $user = UserFactory::update($user, $email, $password, $roles);

        $this->userRepository->save($user);
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->findAllUsers();
    }

    public function getUserById(string $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function deleteUser(User $user): void
    {
        $this->userRepository->delete($user);
    }
}
