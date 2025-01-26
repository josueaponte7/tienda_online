<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

final class UserRegisterRequest
{
    private string $email;
    private string $password;

    public function __construct(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) && !isset($data['password'])) {
            throw new \InvalidArgumentException('Email and password are required.');
        }

        $this->email = $data['email'];
        $this->password = $data['password'];
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
