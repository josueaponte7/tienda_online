<?php

declare(strict_types=1);

namespace App\DTO;

class ProxyUser
{
    public function __construct(
        public ?string $email = null,
        public ?string $password = null,
        public array $roles = []
    ) {}
}
