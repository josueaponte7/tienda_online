<?php

namespace App\Repository;

use App\Entity\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function existsByEmail(string $email): bool;

    public function save(User $user): void;

    public function findAllUsers(): array;

    public function delete(User $user): void;
}
