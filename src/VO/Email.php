<?php

declare(strict_types=1);

namespace App\VO;

use InvalidArgumentException;

final class Email
{
    private string $value;

    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format.');
        }
        $this->value = $email;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
