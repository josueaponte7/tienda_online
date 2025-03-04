<?php

declare(strict_types=1);

namespace App\Request\Admin;

use Symfony\Component\HttpFoundation\Request;

final class UserRegisterRequest
{
    private string $email;
    private string $password;
    private array $roles;

    public function __construct(string $email, string $password, array $roles = ['ROLE_USER'])
    {
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
    }

    public static function fromRequest(Request $request): self
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $rolesString = $request->request->get('roles', 'ROLE_USER');
        $rolesArray = array_map('trim', explode(',', $rolesString));

        return new self($email, $password, $rolesArray);
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
