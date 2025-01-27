<?php

declare(strict_types=1);

namespace App\Request\Api;

use InvalidArgumentException;
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
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid JSON format.');
        }

        $email = $data['email'] ?? throw new InvalidArgumentException('Email is required.');
        $password = $data['password'] ?? throw new InvalidArgumentException('Password is required.');

        $rolesString = $data['roles'] ?? '';

        $roles = strlen($rolesString) == 0
            ? []
            : array_map('trim', explode(',', $rolesString));
        return new self($email, $password, $roles);
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
