<?php

namespace App\Repository;

use App\Entity\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function existsByEmail(string $email): bool;
    public function save(User $user): void;
}
