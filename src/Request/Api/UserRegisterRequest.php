<?php

declare(strict_types=1);

namespace App\Request\Api;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

final class UserRegisterRequest
{
    private string $email;
    private string $password;
    /** @var string[] */
    private array $roles;

    /**
     * @param string[] $roles
     */
    public function __construct(string $email, string $password, array $roles = ['ROLE_USER'])
    {
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
    }

    public static function fromRequest(Request $request): self
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new InvalidArgumentException('Invalid JSON format.');
        }
        $email = isset($data['email']) && is_string($data['email'])
            ? $data['email']
            : throw new InvalidArgumentException('Email es obligatorio, y debe ser una cadena de texto.');

        $password = $data['password'] ?? throw new InvalidArgumentException('Password es obligatorio.');

        $rolesString = $data['roles'] ?? '';

        $roles = $rolesString === ''
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

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
