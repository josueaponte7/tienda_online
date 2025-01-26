<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

final class UserRegisterRequest
{
    private string $email;
    private string $password;
    private array $roles;

    public function __construct(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) && !isset($data['password'])) {
            throw new \InvalidArgumentException('Email and password are required.');
        }

        $this->email = $data['email'];
        $this->password = $data['password'];
        $rolesString = $data['roles'] ?? '';
        $this->roles = $this->processRoles($rolesString);
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

    private function processRoles(mixed $rolesString): array
    {
        if (is_array($rolesString)) {
            $rolesString = implode(',', $rolesString);
        }

        return $rolesString
            ? array_map('trim', explode(',', $rolesString))
            : ['ROLE_USER']; // Valor predeterminado si no hay roles
    }


}
