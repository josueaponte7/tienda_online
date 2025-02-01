<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;

readonly class UserRepositoryMysql
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u');
    }

    public function findByEmail(string $email): ?User
    {
        return $this->createQueryBuilder()
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function existsByEmail(string $email): bool
    {
        return (bool)$this->createQueryBuilder()
            ->select('1')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(User $user): void
    {
        $this->logger->info('Guardando usuario en MySQL: ' . $user->getEmail());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->detach($user);
    }

    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $this->entityManager->detach($user);
    }

    public function findById(string $id): ?User
    {
        return $this->createQueryBuilder()
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllUsers(): array
    {
        return $this->createQueryBuilder()
            ->select('u.id, u.email, u.roles')
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
