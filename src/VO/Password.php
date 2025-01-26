<?php

declare(strict_types=1);

namespace App\VO;

final class Password
{
    private string $hashedPassword;

    public function __construct(string $password, bool $hashed = false)
    {
        if ($hashed) {
            $this->hashedPassword = $password;
        } else {
            $this->hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    public function getValue(): string
    {
        return $this->hashedPassword;
    }

    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->hashedPassword);
    }
}
