<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepositoryMysql;
use App\Repository\UserRepositoryPostgres;
use App\VO\Email;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class UserSyncService
{
    public function __construct(
        private UserRepositoryMysql $mysqlRepo,
        private UserRepositoryPostgres $postgresRepo,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Actualiza el email de un usuario en ambas bases de datos.
     */
    public function updateUserEmail(string $id, string $newEmail): void
    {
        $this->logger->info("Actualizando usuario con ID: $id en ambas bases de datos.");

        // Obtener el usuario desde ambas bases de datos
        $mysqlUser = $this->mysqlRepo->findById($id);
        $postgresUser = $this->postgresRepo->findById($id);

        if (!$mysqlUser || !$postgresUser) {
            throw new \Exception("Usuario con ID $id no encontrado en una o ambas bases de datos.");
        }

        // Sincronizar el email
        $mysqlUser->setEmail(new Email($newEmail));
        $postgresUser->setEmail(new Email($newEmail));

        // Guardar los cambios en ambas bases
        $this->mysqlRepo->save($mysqlUser);
        $this->postgresRepo->save($postgresUser);

        $this->logger->info("Usuario con ID: $id actualizado correctamente en ambas bases de datos.");
    }

    /**
     * Elimina un usuario de ambas bases de datos.
     */
    public function deleteUser(string $id): void
    {
        $this->logger->info("Eliminando usuario con ID: $id de ambas bases de datos.");

        $mysqlUser = $this->mysqlRepo->findById($id);
        $postgresUser = $this->postgresRepo->findById($id);

        if ($mysqlUser && $postgresUser) {
            $this->mysqlRepo->delete($mysqlUser);
            $this->postgresRepo->delete($postgresUser);
        } else {
            throw new \Exception("Usuario con ID $id no encontrado en una o ambas bases de datos.");
        }

        $this->logger->info("Usuario con ID: $id eliminado correctamente de ambas bases de datos.");
    }
}
