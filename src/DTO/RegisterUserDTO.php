<?php

declare(strict_types=1);

namespace App\DTO;

final class RegisterUserDTO
{
    public string $email;
    public string $password;
    public array $roles;

    public function __construct(string $email, string $password, array $roles = ['ROLE_USER'])
    {
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }
}
