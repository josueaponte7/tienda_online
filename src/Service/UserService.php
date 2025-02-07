<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RegisterUserDTO;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepositoryMysql;
use App\Repository\UserRepositoryPostgres;
use App\VO\Email;
use App\VO\Password;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;

readonly class UserService
{
    public function __construct(
        private UserRepositoryMysql $userRepositoryMysql,
        private UserRepositoryPostgres $userRepositoryPostgres,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private LoggerService $loggerService,
    ) {
    }

    /**
     * Registra un nuevo usuario en ambas bases de datos.
     */
    public function registerUser(RegisterUserDTO $dto): User
    {
        $this->loggerService->logInfo("Intentando registrar usuario: {$dto->getEmail()}");
        $email = new Email($dto->email);

        // Verificar existencia en MySQL y PostgresSQL
        if ($this->userRepositoryMysql->existsByEmail($email->getValue()) ||
            $this->userRepositoryPostgres->existsByEmail($email->getValue())) {
            $this->loggerService->logError("El usuario ya existe: {$dto->getEmail()}");
            throw new RuntimeException('User already exists.');
        }

        // Crear el usuario
        $password = new Password($dto->password);
        $user = UserFactory::create($email, $password, $dto->roles->getValue());

        // Guardar en ambas bases dentro de una transacción distribuida
        try {
            $this->entityManager->beginTransaction();

            $this->userRepositoryMysql->save($user);
            $this->userRepositoryPostgres->save($user);

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->loggerService->logError("Error al registrar usuario: {$e->getMessage()}");
            throw $e;
        }

        return $this->userRepositoryMysql->findByEmail($dto->email);
    }

    /**
     * Actualiza los datos de un usuario en ambas bases de datos.
     */
    public function updateUser(string $id, RegisterUserDTO $dto): User
    {
        $this->loggerService->logInfo("Actualizando usuario con ID: $id");

        $userMysql = $this->userRepositoryMysql->findById($id);
        $userPostgres = $this->userRepositoryPostgres->findById($id);

        if (!$userMysql && !$userPostgres) {
            $this->loggerService->logError("Usuario con ID $id no encontrado.");
            throw new RuntimeException('Usuario no encontrado.');
        }

        $email = new Email($dto->getEmail());
        $password = $dto->getPassword() ? new Password($dto->getPassword()) : null;
        $roles = !empty($dto->getRoles()->getValue()) ? $dto->getRoles()->getValue() : null;

        // Actualizar el usuario en ambas bases
        try {
            $this->entityManager->beginTransaction();

            if ($userMysql) {
                $userMysql = UserFactory::update($userMysql, $email, $password, $roles);
                $this->userRepositoryMysql->save($userMysql);
                $this->loggerService->logInfo('MYSQL: Usuario actualizado exitosamente.', [
                    'email' => $dto->getEmail(),
                    'timestamp' => date('c'),
                ]);
            }

            if ($userPostgres) {
                $userPostgres = UserFactory::update($userPostgres, $email, $password, $roles);
                $this->userRepositoryPostgres->save($userPostgres);
                $this->loggerService->logInfo('POSTGRESQL: Usuario actualizado exitosamente.', [
                    'email' => $dto->getEmail(),
                    'timestamp' => date('c'),
                ]);
            }

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->loggerService->logError("Error al actualizar usuario: {$e->getMessage()}");
            throw $e;
        }
        return $this->userRepositoryMysql->findById($id);
    }

    /**
     * Elimina un usuario de ambas bases de datos.
     */
    public function deleteUser(string $id): void
    {
        $this->logger->info("Eliminando usuario con ID: $id");

        $userMysql = $this->userRepositoryMysql->findById($id);
        $userPostgres = $this->userRepositoryPostgres->findById($id);

        if (!$userMysql && !$userPostgres) {
            $this->logger->error("Usuario con ID $id no encontrado.");
            throw new RuntimeException('Usuario no encontrado.');
        }

        try {
            $this->entityManager->beginTransaction();

            if ($userMysql) {
                $this->userRepositoryMysql->delete($userMysql);
                $this->loggerService->logInfo('MYSQL: Usuario eliminado exitosamente.', [
                    'id' => $id,
                    'timestamp' => date('c'),
                ]);
            }

            if ($userPostgres) {
                $this->userRepositoryPostgres->delete($userPostgres);
                $this->loggerService->logInfo('POSTGRESQL: Usuario eliminado exitosamente.', [
                    'id' => $id,
                    'timestamp' => date('c'),
                ]);
            }

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            $this->logger->error("Error al eliminar usuario: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Obtiene todos los usuarios desde MySQL.
     */
    public function getAllUsers(): array
    {
        return $this->userRepositoryMysql->findAllUsers();
    }

    /**
     * Obtiene un usuario por su ID.
     */
    public function getUserById(string $id): ?User
    {
        return $this->userRepositoryMysql->findById($id);
    }
}
