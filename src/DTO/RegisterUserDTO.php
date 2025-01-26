<?php

declare(strict_types=1);

namespace App\DTO;

use App\VO\Roles;

final class RegisterUserDTO
{
    public string $email;
    public string $password;
    public Roles $roles;

    public function __construct(string $email, string $password, array $roles = [])
    {
        $this->email = $email;
        $this->password = $password;
        $this->roles = new Roles($roles);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): Roles
    {
        return $this->roles;
    }
}
