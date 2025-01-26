<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use App\VO\Email;
use App\VO\Password;
use App\VO\Roles;

final class UserFactory
{
    public static function create(Email $email, Password $password, array $roles = ['ROLE_USER']): User
    {
        return User::fromValueObjects($email, $password, new Roles($roles));
    }

    public static function update(User $user, Email $email, ?Password $password = null, ?array $roles = null): User
    {
        $user->setEmail($email);

        if ($password !== null) {
            $user->setPassword($password);
        }

        if ($roles !== null) {
            $user->setRoles(new Roles($roles));
        }

        return $user;
    }
}

