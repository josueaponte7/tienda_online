<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use App\VO\Email;
use App\VO\Password;

final class UserFactory
{
    public static function create(Email $email, Password $password, array $roles = ['ROLE_USER']): User
    {
        return User::fromValueObjects($email, $password, $roles);
    }
}
